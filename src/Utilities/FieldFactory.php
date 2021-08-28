<?php

namespace Leuchtturm\Utilities;

use Curfle\Agreements\Auth\Guardian;
use Curfle\DAO\Relationships\ManyToManyRelationship;
use Curfle\DAO\Relationships\OneToManyRelationship;
use Curfle\Support\Facades\Auth;
use GraphQL\Arguments\GraphQLFieldArgument;
use GraphQL\Errors\UnauthenticatedError;
use GraphQL\Fields\GraphQLTypeField;
use GraphQL\Types\GraphQLBoolean;
use GraphQL\Types\GraphQLInt;
use GraphQL\Types\GraphQLList;
use GraphQL\Types\GraphQLNonNull;
use GraphQL\Types\GraphQLString;
use GraphQL\Types\GraphQLType;
use Leuchtturm\LeuchtturmException;
use ReflectionException;

class FieldFactory
{
    const CREATE = "CREATE";
    const READ = "READ";
    const UPDATE = "UPDATE";
    const DELETE = "DELETE";
    const ALL = "ALL";

    /**
     * Name of the GraphQLTypeField. E.g. "deleteUser".
     *
     * @var string
     */
    private string $name;

    /**
     * Pure name of the GraphQLTypeField. E.g. "user".
     *
     * @var string
     */
    private string $pureName;

    /**
     * Description of the GraphQLTypeField.
     *
     * @var string
     */
    private string $description;

    /**
     * DAO class name.
     *
     * @var string
     */
    private string $dao;

    /**
     * CRUD operation of the field.
     *
     * @var string
     */
    private string $operation;

    /**
     * Guardian that protects the field.
     *
     * @var string|null
     */
    private ?string $guardian = null;

    /**
     * Guardian that verifies the ownership and protects the field.
     *
     * @var string|null
     */
    private ?string $ownerGuardian = null;

    /**
     * TypeFactory for building type and input type of the GraphQLTypeField.
     *
     * @var TypeFactory
     */
    private TypeFactory $typeFactory;

    public function build(): GraphQLTypeField
    {
        // create field
        return new GraphQLTypeField(
            $this->name,
            $this->buildReturnType(),
            $this->description,
            $this->buildResolve(),
            $this->buildArgs()
        );
    }

    /**
     * Builds the return type for the field.
     *
     * @return GraphQLType
     * @throws LeuchtturmException
     * @throws ReflectionException
     */
    private function buildReturnType(): GraphQLType
    {
        return match ($this->operation) {
            FieldFactory::CREATE, FieldFactory::READ => $this->typeFactory->build(),
            FieldFactory::ALL => new GraphQLNonNull(new GraphQLList(new GraphQLNonNull($this->typeFactory->build()))),
            FieldFactory::UPDATE, FieldFactory::DELETE => new GraphQLNonNull(new GraphQLBoolean()),
        };
    }

    /**
     * Builds the return type for the field.
     *
     * @return GraphQLType
     * @throws LeuchtturmException
     * @throws ReflectionException
     */
    private function buildInputType(): GraphQLType
    {
        return $this->typeFactory->buildInput();
    }

    /**
     * Builds the resolve function for the field.
     *
     * @return \Closure
     */
    private function buildResolve(): \Closure
    {
        $this_ = $this;
        $dao = $this->dao;
        $pureName = $this->pureName;
        $hasOne = $this->typeFactory->getHasOne();
        $hasMany = $this->typeFactory->getHasMany();
        return match ($this->operation) {
            FieldFactory::CREATE => function ($parent, $args) use ($dao, $pureName, $hasMany) {
                // check permissions
                $this->validateRequestWithGuardian();

                // store ids to other relations
                $relationsToAdd = [];
                foreach ($hasMany as $field => $value) {
                    if (array_key_exists($field, $args)) {
                        $relationsToAdd[$field] = $args[$field];
                        unset($args[$field]);
                    }
                }

                // create the actual entry
                $entry = call_user_func("$dao::create", $args[$pureName]);

                // add relation
                foreach ($relationsToAdd as $argument => $ids) {
                    $relationship = $entry->{$argument}();
                    foreach ($ids as $id) {
                        if ($relationship instanceof OneToManyRelationship)
                            $relationship->associate(call_user_func("{$hasMany[$argument]}::get", $id));
                        if ($relationship instanceof ManyToManyRelationship)
                            $relationship->attach(call_user_func("{$hasMany[$argument]}::get", $id));
                    }
                }

                return $entry;
            },
            FieldFactory::READ => function ($parent, $args) use ($dao) {
                // check permissions
                $this->validateRequestWithGuardian($args["id"]);

                return call_user_func("$dao::get", $args["id"]);
            },
            FieldFactory::UPDATE => function ($parent, $args) use ($dao, $pureName, $hasMany) {
                // check permissions
                $this->validateRequestWithGuardian($args["id"]);

                // store ids to other relations
                $relationsToAdd = [];
                foreach ($hasMany as $field => $value) {
                    if (array_key_exists($field, $args)) {
                        $relationsToAdd[$field] = $args[$field];
                        unset($args[$field]);
                    }
                }

                // get entry
                $entry = call_user_func("$dao::get", $args["id"]);
                foreach ($args[$pureName] as $property => $value)
                    $entry->{$property} = $value;


                // update relations
                foreach ($relationsToAdd as $argument => $ids) {
                    $relationship = $entry->{$argument}();

                    // remove old entries
                    if ($relationship instanceof OneToManyRelationship) {
                        foreach ($ids as $id) {
                            $fkPropertyColumn = "{$pureName}_id";
                            $relatedEntry = call_user_func("{$hasMany[$argument]}::where", $fkPropertyColumn, $entry->id)
                                ->update([$fkPropertyColumn => null]);
                        }
                    }
                    if ($relationship instanceof ManyToManyRelationship)
                        $relationship->detach();

                    // connect new entries
                    foreach ($ids as $id) {
                        if ($relationship instanceof OneToManyRelationship) {
                            $relationship->associate(call_user_func("{$hasMany[$argument]}::get", $id));
                        }
                        if ($relationship instanceof ManyToManyRelationship)
                            $relationship->attach(call_user_func("{$hasMany[$argument]}::get", $id));
                    }
                }

                return $entry->update();
            },
            FieldFactory::DELETE => function ($parent, $args) use ($dao, $pureName) {
                // check permissions
                $this->validateRequestWithGuardian($args["id"]);

                // delete entry
                $entry = call_user_func("$dao::get", $args["id"]);
                return $entry->delete();
            },
            FieldFactory::ALL => function ($parent) use ($dao, $this_) {
                // check permissions
                $this->validateRequestWithGuardian();

                return call_user_func("$dao::all");
            },
        };
    }

    /**
     * Builds the arguments for the field.
     *
     * @return array
     * @throws LeuchtturmException
     * @throws ReflectionException
     */
    private function buildArgs(): array
    {
        $dao = $this->dao;
        $pureName = $this->pureName;
        return match ($this->operation) {
            FieldFactory::ALL => [],
            FieldFactory::CREATE => [
                new GraphQLFieldArgument($pureName, new GraphQLNonNull($this->buildInputType()))
            ],
            FieldFactory::UPDATE => [
                new GraphQLFieldArgument("id", new GraphQLNonNull(new GraphQLInt())),
                new GraphQLFieldArgument($pureName, new GraphQLNonNull($this->buildInputType()))
            ],
            FieldFactory::READ, FieldFactory::DELETE => [
                new GraphQLFieldArgument("id", new GraphQLNonNull(new GraphQLInt()))
            ],
        };
    }

    /**
     * @param string $name
     * @return FieldFactory
     */
    public function name(string $name): FieldFactory
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $description
     * @return FieldFactory
     */
    public function description(string $description): FieldFactory
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $operation
     * @return FieldFactory
     */
    public function operation(string $operation): FieldFactory
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @param TypeFactory $typeFactory
     * @return FieldFactory
     */
    public function typeFactory(TypeFactory $typeFactory): FieldFactory
    {
        $this->typeFactory = $typeFactory;
        return $this;
    }

    /**
     * @param string $dao
     * @return FieldFactory
     */
    public function dao(string $dao): FieldFactory
    {
        $this->dao = $dao;
        return $this;
    }

    /**
     * @param string $pureName
     * @return FieldFactory
     */
    public function pureName(string $pureName): FieldFactory
    {
        $this->pureName = $pureName;
        return $this;
    }

    /**
     * Sets the guardian that protects the route.
     *
     * @param string $name
     * @return $this
     */
    public function guardian(string $name = "default"): static
    {
        $this->guardian = $name;
        return $this;
    }

    /**
     * Ensures that the id of the logged-in user, provided by a guardian, equals
     * the id passed to the field "id"-parameter.
     *
     * @return $this
     */
    public function onlyOwner(string $guardianName = "default"): static
    {
        $this->ownerGuardian = $guardianName;
        return $this;
    }

    /**
     * Validates the current request and throws a UnauthenticatedError-GraphQLError if
     * the request is not allowed to access this field.
     *
     * @throws UnauthenticatedError
     */
    private function validateRequestWithGuardian(mixed $id = null): void
    {
        // check if guardian is set.
        if ($this->guardian !== null) {
            // check if guardian exists and request not valid against guardian.
            if (Auth::guardian($this->guardian) !== null
                && !Auth::guardian($this->guardian)->validate(app("request"))) {

                // if the guardian cannot verify the request, check if owner guardian is set.
                // if that is not the case, throw an UnauthenticatedError - but if it is, ensure
                // that the requestor is the owner of the entry.
                if ($this->ownerGuardian === null)
                    throw new UnauthenticatedError("Access denied");
                else if (Auth::guardian($this->ownerGuardian) !== null
                    && (
                        !Auth::guardian($this->ownerGuardian)->validate(app("request"))
                        || Auth::guardian($this->ownerGuardian)->user()->getIdentifier() !== $id
                    ))
                    throw new UnauthenticatedError("Access denied");
            }
        } // else check if ownership guardian is set, exists, request is not valid or not the owner
        else if ($this->ownerGuardian !== null
            && Auth::guardian($this->ownerGuardian) !== null
            && (
                !Auth::guardian($this->ownerGuardian)->validate(app("request"))
                || Auth::guardian($this->ownerGuardian)->user()->getIdentifier() !== $id
            ))
            throw new UnauthenticatedError("Access denied");
    }
}
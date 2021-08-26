<?php

namespace Leuchtturm\Utilities;

use GraphQL\Arguments\GraphQLFieldArgument;
use GraphQL\Fields\GraphQLTypeField;
use GraphQL\Types\GraphQLBoolean;
use GraphQL\Types\GraphQLFloat;
use GraphQL\Types\GraphQLInputObjectType;
use GraphQL\Types\GraphQLInt;
use GraphQL\Types\GraphQLList;
use GraphQL\Types\GraphQLNonNull;
use GraphQL\Types\GraphQLObjectType;
use GraphQL\Types\GraphQLString;
use GraphQL\Types\GraphQLType;
use Leuchtturm\LeuchtturmException;
use Leuchtturm\LeuchtturmManager;
use Leuchtturm\Utilities\Reflection\ReflectionProperty;
use ReflectionException;

class TypeFactory
{
    /**
     * Name of the GraphQLType.
     *
     * @var string
     */
    private string $name;

    /**
     * Description of the GraphQLType.
     *
     * @var string
     */
    private string $description = "";

    /**
     * Class name of the DAO class.
     *
     * @var string
     */
    private string $dao;

    /**
     * Fields to be ignored.
     *
     * @var array
     */
    private array $ignore = ["connector"];

    /**
     * List relations to other DAO classes, given by their typenames.
     * Properties that cannot be detected automatically.
     *
     * @var array
     */
    private array $hasMany = [];

    /**
     * Relations to other DAO classes, given by theit typenames.
     * Properties that cannot be detected automatically.
     *
     * @var array
     */
    private array $hasOne = [];

    /**
     * Class properties, used for caching puposes.
     *
     * @var ?array
     */
    private ?array $properties = null;

    /**
     * LeuchtturmManager instance for resolviong other type factories.
     *
     * @var LeuchtturmManager
     */
    private LeuchtturmManager $manager;

    /**
     * The resulting GraphQLType, stored for caching purposes.
     *
     * @var GraphQLObjectType|null
     */
    private ?GraphQLObjectType $graphQLType = null;

    /**
     * The resulting GraphQLInputObjectType, stored for caching purposes.
     *
     * @var GraphQLInputObjectType|null
     */
    private ?GraphQLInputObjectType $graphQLInputType = null;

    public function __construct(LeuchtturmManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sets the type name.
     *
     * @param string $name
     * @return TypeFactory
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the type description.
     *
     * @param string $description
     * @return TypeFactory
     */
    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Sets the DAO class name.
     *
     * @param string $dao
     * @return TypeFactory
     */
    public function setDAO(string $dao): static
    {
        $this->dao = $dao;
        return $this;
    }

    /**
     * Ignore properties of DAO.
     *
     * @param string|array $properties
     * @return TypeFactory
     */
    public function ignore(string|array $properties): static
    {
        if (is_array($properties))
            $this->ignore = array_merge($this->ignore, $properties);
        else
            $this->ignore[] = $properties;
        return $this;
    }

    /**
     * Adds a field with a GraphQLList of a TypeFactory.
     *
     * @param string $fieldname
     * @param string $oftype
     * @return TypeFactory
     */
    public function hasMany(string $fieldname, string $oftype): static
    {
        $this->hasMany[$fieldname] = $oftype;
        return $this;
    }

    /**
     * Adds a field of a TypeFactory.
     *
     * @param string $fieldname
     * @param string $oftype
     * @return TypeFactory
     */
    public function hasOne(string $fieldname, string $oftype): static
    {
        $this->hasOne[$fieldname] = $oftype;
        return $this;
    }

    /**
     * Returns the class properties.
     *
     * @return array
     * @throws ReflectionException
     */
    private function getProperties(): array
    {
        if ($this->properties !== null)
            return $this->properties;
        return $this->properties = Inspector::getProperties($this->dao);
    }

    /**
     * Builds the GraphQLObjectType.
     *
     * @return GraphQLObjectType
     * @throws ReflectionException
     * @throws LeuchtturmException
     */
    public function build(): GraphQLObjectType
    {
        // check if cache can be used
        if ($this->graphQLType !== null)
            return $this->graphQLType;

        $fields = [];
        $properties = $this->getProperties();

        $this->graphQLType = new GraphQLObjectType(
            $this->name,
            $this->description,
            function () use (&$fields) {
                return $fields;
            }
        );

        $this->collectFieldsFromClassDoc();

        $fields = array_merge($fields, $this->buildFieldsFromProperty($properties));
        $fields = array_merge($fields, $this->buildFieldsFromHasOne());
        $fields = array_merge($fields, $this->buildFieldsFromHasMany());

        return $this->graphQLType;
    }

    /**
     * Builds the GraphQLInputObjectType.
     *
     * @return GraphQLInputObjectType
     * @throws LeuchtturmException
     * @throws ReflectionException
     */
    public function buildInput(): GraphQLInputObjectType
    {
        // check if cache can be used
        if ($this->graphQLInputType !== null)
            return $this->graphQLInputType;

        $fields = [];
        $properties = $this->getProperties();

        $this->graphQLInputType = new GraphQLInputObjectType(
            $this->name . "Input",
            $this->description,
            function () use (&$fields) {
                return $fields;
            }
        );

        $this->collectFieldsFromClassDoc();

        // collect fields from properties but do not ignore the field if it is the id of a hasOne relation
        // because those fields can be directly filled with a scalar value and need no extra input type like
        // a hasMany relationship does with a GraphQLList(GraphQLInt).
        $fields = array_merge($fields, $this->buildFieldsFromProperty($properties, true));
        $fields = array_merge($fields, $this->buildInputFieldsFromHasMany());

        return $this->graphQLInputType;
    }

    /**
     * Collects field from the class doc and uses those to add hasMany and hasOne relations.
     *
     * @throws ReflectionException
     */
    private function collectFieldsFromClassDoc()
    {
        $properties = Inspector::getPropertiesFromClassDoc($this->dao);
        foreach ($properties as $property) {
            if ($property->isArrayType())
                $this->hasMany[$property->getName()] = $property->getType();
            else
                $this->hasOne[$property->getName()] = $property->getType();
        }
    }

    /**
     *
     * @throws ReflectionException|LeuchtturmException
     */
    private function buildFieldsFromHasMany(): array
    {
        $fields = [];
        $manager = $this->manager;

        foreach ($this->hasMany as $fieldname => $typename) {
            $fields[] = new GraphQLTypeField(
                $fieldname,
                new GraphQLNonNull(new GraphQLList($this->manager->build($typename))),
            );
        }

        return $fields;
    }

    /**
     *
     */
    private function buildinputFieldsFromHasMany(): array
    {
        $fields = [];
        $manager = $this->manager;

        foreach ($this->hasMany as $fieldname => $typename) {
            $fields[] = new GraphQLTypeField(
                $fieldname,
                new GraphQLList(new GraphQLNonNull(new GraphQLInt())),
            );
        }

        return $fields;
    }

    /**
     * Builds several GraphQLTypeFields from the has-one relationships.
     *
     * @throws ReflectionException
     * @throws LeuchtturmException
     */
    private function buildFieldsFromHasOne(): array
    {
        $fields = [];
        $manager = $this->manager;

        $properties = $this->getProperties();

        foreach ($this->hasOne as $fieldname => $dao) {
            // check if fk exists and is non-null
            $isNonNull = false;
            $potentialFK = "{$fieldname}_id";
            if (array_key_exists($potentialFK, $properties) && !$properties[$potentialFK]->isNullable())
                $isNonNull = true;

            $fields[] = new GraphQLTypeField(
                $fieldname,
                $isNonNull
                    ? new GraphQLNonNull($this->manager->build($dao))
                    : $this->manager->build($dao),
                resolve: function ($parent) use ($manager, $fieldname, $dao) {
                    $daoClass = $manager->factory($dao)->getDAO();
                    $property = "{$fieldname}_id";
                    return call_user_func("$daoClass::get", $parent->{$property});
                }
            );
        }

        return $fields;
    }

    /**
     * Builds several GraphQLTypeFields from the properties.
     *
     * @param array $properties
     * @param bool $excludeHasOneRelations
     * @return array
     * @throws LeuchtturmException
     */
    private function buildFieldsFromProperty(array $properties, bool $excludeHasOneRelations = false): array
    {
        $fields = [];

        foreach ($properties as $property) {
            // check if property is allowed and not in $hasMany or $hasOne
            if (!in_array($property->getName(), $this->ignore)
                && !array_key_exists(Str::removeIn("_id", $property->getName()), $this->hasMany)
                && (
                    $excludeHasOneRelations
                    || !array_key_exists(Str::removeIn("_id", $property->getName()), $this->hasOne)
                )) {
                $fields[] = $this->buildFieldFromProperty($property);
            }
        }

        return $fields;
    }

    /**
     * Builds a GraphQLTypeField from a property.
     *
     * @param ReflectionProperty $property
     * @return GraphQLTypeField
     * @throws LeuchtturmException
     */
    private function buildFieldFromProperty(ReflectionProperty $property): GraphQLTypeField
    {
        return new GraphQLTypeField(
            $property->getName(),
            $this->buildTypeFromProperty($property),
            defaultValue: $property->getDefaultValue()
        );
    }

    /**
     * Builds a GraphQLType from a property.
     *
     * @param ReflectionProperty $property
     * @param bool $ignoreMissingDefaultValue
     * @return GraphQLType
     * @throws LeuchtturmException
     */
    private function buildTypeFromProperty(ReflectionProperty $property, bool $ignoreMissingDefaultValue = false): GraphQLType
    {
        // handle arrays
        if ($property->getType() === "array"
            || $property->getType() === "?array")
            throw new LeuchtturmException("The property {$property->getName()} is of type array which correlates to a GraphQLList, which is not supported in auto-generation.");

        return match (strtolower($property->getType())) {
            "string" => new GraphQLNonNull(new GraphQLString()),
            "?string" => new GraphQLString(),
            "int" => new GraphQLNonNull(new GraphQLInt()),
            "?int" => new GraphQLInt(),
            "float" => new GraphQLNonNull(new GraphQLFloat()),
            "?float" => new GraphQLFloat(),
            "bool" => new GraphQLNonNull(new GraphQLBoolean()),
            "?bool" => new GraphQLBoolean(),
        };
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDAO(): string
    {
        return $this->dao;
    }

    /**
     * @return array
     */
    public function getHasMany(): array
    {
        return $this->hasMany;
    }

    /**
     * @return array
     */
    public function getHasOne(): array
    {
        return $this->hasOne;
    }


}
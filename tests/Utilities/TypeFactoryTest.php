<?php

namespace Tests\Utilities;

use GraphQL\Types\GraphQLBoolean;
use GraphQL\Types\GraphQLInt;
use GraphQL\Types\GraphQLNonNull;
use GraphQL\Types\GraphQLString;
use Leuchtturm\LeuchtturmException;
use Leuchtturm\LeuchtturmManager;
use Leuchtturm\Utilities\Inspector;
use Leuchtturm\Utilities\TypeFactory;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Tests\Resources\Classes\Login;
use Tests\Resources\Classes\User;
use function PHPUnit\Framework\assertEquals;

class TypeFactoryTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws LeuchtturmException
     */
    public function testTypeFactory()
    {
        $manager = new LeuchtturmManager();
        $factory = $manager->create(User::class);
        $graphQLObjectType = $factory->build();
        $fields = $graphQLObjectType->getFields();

        $this->assertArrayHasKey("id", $fields);
        $this->assertArrayHasKey("firstname", $fields);
        $this->assertArrayHasKey("lastname", $fields);
        $this->assertArrayHasKey("email", $fields);
        $this->assertArrayHasKey("role", $fields);
        $this->assertArrayHasKey("rights", $fields);
        $this->assertArrayHasKey("confirmed", $fields);
        $this->assertArrayHasKey("birthday", $fields);

        $this->assertEquals(
            new GraphQLNonNull(new GraphQLInt()),
            $fields["id"]->getType()
        );
        $this->assertEquals(
            new GraphQLNonNull(new GraphQLString()),
            $fields["firstname"]->getType()
        );
        $this->assertEquals(
            new GraphQLNonNull(new GraphQLString()),
            $fields["lastname"]->getType()
        );
        $this->assertEquals(
            new GraphQLNonNull(new GraphQLString()),
            $fields["email"]->getType()
        );
        $this->assertEquals(
            new GraphQLNonNull(new GraphQLString()),
            $fields["role"]->getType()
        );
        $this->assertEquals(
            new GraphQLNonNull(new GraphQLInt()),
            $fields["rights"]->getType()
        );
        $this->assertEquals(
            new GraphQLNonNull(new GraphQLBoolean()),
            $fields["confirmed"]->getType()
        );
        $this->assertEquals(
            new GraphQLString(),
            $fields["birthday"]->getType()
        );
    }

    /**
     * @throws ReflectionException
     * @throws LeuchtturmException
     */
    public function testTypeFactoryHasManyAndHasOne()
    {
        $manager = new LeuchtturmManager();
        $factoryUser = $manager->create(User::class)->hasMany("logins", Login::class);
        $factoryLogin = $manager->create( Login::class)->hasOne("user", User::class);
        $graphQLObjectTypeLogin = $factoryLogin->build();
        $graphQLObjectTypeUser = $factoryUser->build();

        $this->assertEquals(
            ["id", "timestamp", "user"],
            array_keys($graphQLObjectTypeLogin->getFields())
        );

        $this->assertEquals(
            $graphQLObjectTypeUser,
            $graphQLObjectTypeLogin->getFields()["user"]
                ->getType() // GraphQLNonNull
                ->getInnerType() // Benutzer-Type
        );
        $this->assertEquals(
            $graphQLObjectTypeLogin,
            $graphQLObjectTypeUser->getFields()["logins"]
                ->getType() // GraphQLNonNull
                ->getInnerType() // GraphQLList
                ->getInnerType() // GraphQLNonNull
                ->getInnerType() // Benutzer-Type
        );
    }
}
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
use Leuchtturm\Vocab\English;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Tests\Resources\Classes\Login;
use Tests\Resources\Classes\User;
use function PHPUnit\Framework\assertEquals;

class FieldFactoryTest extends TestCase
{
    /**
    * @throws ReflectionException
     */
    public function testSerialization()
    {
        $manager = new LeuchtturmManager();
        $manager->setVocab(new English());
        $field = $manager->C(User::class)->pre(function(){
            return 42;
        });

        $this->assertIsString(serialize($field));

        $fieldClone = unserialize(serialize($field));

        $this->assertEquals(42, $fieldClone->callPre());
    }
}
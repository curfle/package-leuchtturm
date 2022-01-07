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
     */
    public function testSerialization()
    {
        $this->markTestSkipped("Serialization not supported anymore");
    }
}
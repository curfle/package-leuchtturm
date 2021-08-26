<?php

namespace Tests\Utilities;

use Leuchtturm\Utilities\Inspector;
use PHPUnit\Framework\TestCase;
use Tests\Resources\Classes\User;

class InspectorTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testGetFields()
    {
        $properties = Inspector::getProperties(User::class);

        $this->assertSame(
            ["id", "firstname", "lastname", "email", "role", "rights", "confirmed", "birthday", "connector"],
            array_keys($properties)
        );
    }
}
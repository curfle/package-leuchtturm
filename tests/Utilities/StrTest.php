<?php

namespace Tests\Utilities;

use Leuchtturm\Utilities\Inspector;
use Leuchtturm\Utilities\Str;
use PHPUnit\Framework\TestCase;
use Tests\Resources\Classes\User;

class StrTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testRemoveIn()
    {
        $string = "user_id";

        $this->assertSame(
            "user",
            Str::removeIn("_id", $string)
        );
    }
}
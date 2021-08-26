<?php

namespace Leuchtturm\Facades;

use Curfle\Support\Facades\Facade;

class Leuchtturm extends Facade{

    protected static function getFacadeAccessor(): string
    {
        return "Leuchtturm";
    }
}
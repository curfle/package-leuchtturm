<?php

namespace Leuchtturm\Facades;

use Curfle\Support\Facades\Facade;
use GraphQL\Types\GraphQLType;
use Leuchtturm\LeuchtturmManager;
use Leuchtturm\Utilities\FieldFactory;
use Leuchtturm\Utilities\TypeFactory;
use Leuchtturm\Vocab\Vocab;

/**
 * @method LeuchtturmManager setVocab(Vocab $vocab)
 * @method TypeFactory create(string $dao, ?string $typename = null)
 * @method GraphQLType build(string $dao)
 * @method TypeFactory factory(string $dao)
 * @method FieldFactory C(string $dao, ?string $fieldname = null, string $description = "")
 * @method FieldFactory R(string $dao, ?string $fieldname = null, string $description = "")
 * @method FieldFactory U(string $dao, ?string $fieldname = null, string $description = "")
 * @method FieldFactory D(string $dao, ?string $fieldname = null, string $description = "")
 * @method FieldFactory A(string $dao, ?string $fieldname = null, string $description = "")
 * @see \Leuchtturm\LeuchtturmManager
 */
class Leuchtturm extends Facade{

    protected static function getFacadeAccessor(): string
    {
        return "Leuchtturm";
    }
}
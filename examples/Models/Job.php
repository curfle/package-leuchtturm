<?php

namespace Examples\Models;

class Job extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param string|null $name
     */
    public function __construct(public ?string $name="Helloo")
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "job"
        ];
    }
}
<?php

namespace Examples\Models;

use Curfle\DAO\Relationships\ManyToManyRelationship;

/**
 * @property-read Benutzer[] $users
 */
class Rolle extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param string $name
     */
    public function __construct(public string $name)
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "role"
        ];
    }

    /**
     * Returns the associated users.
     *
     * @return ManyToManyRelationship
     */
    public function users() : ManyToManyRelationship
    {
        return $this->belongsToMany(Benutzer::class, "user_role");
    }
}
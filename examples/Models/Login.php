<?php

namespace Examples\Models;

use Curfle\DAO\Relationships\ManyToOneRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read Benutzer $user
 */
class Login extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param int $user_id
     * @param string $timestamp
     */
    public function __construct(
        public int    $user_id,
        public string $timestamp
    )
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "login"
        ];
    }

    /**
     * Returns the associated user.
     *
     * @return ManyToOneRelationship
     */
    public function user() : ManyToOneRelationship
    {
        return $this->belongsTo(Benutzer::class);
    }
}
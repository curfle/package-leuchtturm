<?php

namespace Tests\Resources\Classes;

use Curfle\DAO\Model;
use Curfle\DAO\Relationships\ManyToOneRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read User $user
 * @protect $user userguard
 */
class Login extends Model
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

    public function user(): ManyToOneRelationship
    {
        return $this->hasOne(User::class);
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "user",
        ];
    }
}
<?php

namespace Tests\Resources\Classes;

use Curfle\DAO\Model;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read Login[] $logins
 */
class User extends Model
{

    /**
     * Id of the user.
     *
     * @var int
     */
    public int $id;

    /**
     * @param string $firstname this is teh user's firstname
     * @param string $lastname
     * @param string $email
     * @param string $role
     * @param int $rights
     * @param bool $confirmed
     * @param string|null $birthday
     */
    public function __construct(
        public string  $firstname,
        public string  $lastname,
        public string  $email,
        public string  $role = "USER",
        public int     $rights = 0,
        public bool    $confirmed = false,
        public ?string $birthday = null
    )
    {
    }

    public function logins(): OneToOneRelationship
    {
        return $this->hasMany(Login::class);
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
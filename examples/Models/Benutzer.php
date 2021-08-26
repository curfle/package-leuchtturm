<?php

namespace Examples\Models;

use Curfle\DAO\AuthenticatableModel;
use Curfle\DAO\Relationships\ManyToManyRelationship;
use Curfle\DAO\Relationships\OneToManyRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read Job $job
 * @property-read Login[] $logins
 * @property-read Rolle[] $roles
 */
class Benutzer extends AuthenticatableModel
{

    public int $id;
    public ?string $created;

    /**
     * @param string $firstname
     * @param string $lastname
     * @param string $emaill
     * @param int $job_id
     */
    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $emaill,
        public int    $job_id
    )
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "user",
            "softDelete" => true
        ];
    }

    /**
     * Returns the associated job.
     *
     * @return OneToOneRelationship
     */
    public function job(): OneToOneRelationship
    {
        return $this->hasOne(Job::class);
    }

    /**
     * Returns the associated logins.
     *
     * @return OneToManyRelationship
     */
    public function logins(): OneToManyRelationship
    {
        return $this->hasMany(Login::class);
    }

    /**
     * Returns the associated roles.
     *
     * @return ManyToManyRelationship
     */
    public function roles(): ManyToManyRelationship
    {
        return $this->belongsToMany(Rolle::class, "user_role");
    }
}
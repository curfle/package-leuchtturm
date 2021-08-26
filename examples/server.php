<?php

require __DIR__ . "/../vendor/autoload.php";

use GraphQL\Servers\Server;
use GraphQL\Schemas\Schema;
use GraphQL\Types\GraphQLString;
use GraphQL\Types\GraphQLObjectType;
use GraphQL\Fields\GraphQLTypeField;

// build the query type
$manager = new \Leuchtturm\LeuchtturmManager();
$manager->setVocab(new \Leuchtturm\Vocab\German());

// TODO: should never use create again (only create, read, update, delete)

$QueryType = new GraphQLObjectType("Query", "Root Query", function () use($manager){
    return [
        $manager->R(\Examples\Models\Job::class)->build(),
        $manager->R(\Examples\Models\Login::class)->build(),
        $manager->R(\Examples\Models\Benutzer::class)->build(),
        $manager->R(\Examples\Models\Rolle::class)->build(),
        $manager->A(\Examples\Models\Job::class)->build(),
        $manager->A(\Examples\Models\Login::class)->build(),
        $manager->A(\Examples\Models\Benutzer::class)->build(),
        $manager->A(\Examples\Models\Rolle::class)->build(),
    ];
});

$MutationType = new GraphQLObjectType("Mutation", "Mutation Query", function () use($manager){
    return [
        $manager->C(\Examples\Models\Job::class)->build(),
        $manager->C(\Examples\Models\Login::class)->build(),
        $manager->C(\Examples\Models\Benutzer::class)->build(),
        $manager->C(\Examples\Models\Rolle::class)->build(),
        $manager->U(\Examples\Models\Job::class)->build(),
        $manager->U(\Examples\Models\Login::class)->build(),
        $manager->U(\Examples\Models\Benutzer::class)->build(),
        $manager->U(\Examples\Models\Rolle::class)->build(),
        $manager->D(\Examples\Models\Job::class)->build(),
        $manager->D(\Examples\Models\Login::class)->build(),
        $manager->D(\Examples\Models\Benutzer::class)->build(),
        $manager->D(\Examples\Models\Rolle::class)->build(),
    ];
});

// build the schema
$schema = new Schema($QueryType, $MutationType);

// start a server
$server = new Server($schema);
$server->listen();
# Leuchtturm
Leuchtturm is a package to help you easily build CRUDA-Operations (`create`, `read`, `update`, `delete` and `all`) based GraphQL-apis with `joonlabs\php-graphql` within `Curfle`.

## Installation

```bash
composer require curfle/package-leuchtturm
```

## Example

```php
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
        $manager->R(\Examples\Models\Benutzer::class)->onlyOwner()->build(), // only the owner may read this field
        $manager->R(\Examples\Models\Rolle::class)->build(),
        $manager->A(\Examples\Models\Job::class)->build(),
        $manager->A(\Examples\Models\Login::class)->build(),
        $manager->A(\Examples\Models\Benutzer::class)->build(),
        $manager->A(\Examples\Models\Rolle::class)->build(),
    ];
});

$MutationType = new GraphQLObjectType("Mutation", "Mutation Query", function () use($manager){
    return [
        // all fields are protected by the default guardian
        $manager->C(\Examples\Models\Job::class)->guardian()->build(),
        $manager->C(\Examples\Models\Login::class)->guardian()->build(),
        $manager->C(\Examples\Models\Benutzer::class)->guardian()->build(),
        $manager->C(\Examples\Models\Rolle::class)->guardian()->build(),
        $manager->U(\Examples\Models\Job::class)->guardian()->build(),
        $manager->U(\Examples\Models\Login::class)->guardian()->build(),
        $manager->U(\Examples\Models\Benutzer::class)->guardian()->build(),
        $manager->U(\Examples\Models\Rolle::class)->guardian()->build(),
        $manager->D(\Examples\Models\Job::class)->guardian()->build(),
        $manager->D(\Examples\Models\Login::class)->guardian()->build(),
        $manager->D(\Examples\Models\Benutzer::class)->guardian()->onlyOwner()->build(), // also the owner may delete entries via this field
        $manager->D(\Examples\Models\Rolle::class)->guardian()->build(),
    ];
});

// build the schema
$schema = new Schema($QueryType, $MutationType);

// start a server
$server = new Server($schema);
$server->listen();
```
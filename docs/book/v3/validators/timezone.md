# Timezone Validator

`Laminas\Validator\Timezone` allows validating if an input string represents a
timezone.

## Supported validation types

The `Laminas\Validator\Timezone` validator is capable of validating the
abbreviation (e.g. `ewt`) as well as the location string (e.g.
`America/Los_Angeles`). These options are stored in the validator as
`LOCATION`, `ABBREVIATION`, and `ALL` class constants.

## Basic Usage

The default validation type will check against abbreviations as well as the
location string.

```php
$validator = new Laminas\Validator\Timezone();

$validator->isValid('America/Los_Angeles'); // returns true
$validator->isValid('ewt'); // returns true
$validator->isValid('Foobar');  // returns false
```

To validate against only the location string you can set the type:

```php
use Laminas\Validator\Timezone;

$validator = new Timezone([
    'type' => Timezone::LOCATION,
]);

$validator->isValid('America/Los_Angeles'); // returns true
$validator->isValid('ewt'); // returns false
$validator->isValid('Foobar');  // returns false
```

Similarly, to validate only abbreviations:

```php
use Laminas\Validator\Timezone;

$validator = new Timezone([
    'type' => Timezone::ABBREVIATION,
]);

$validator->isValid('America/Los_Angeles'); // returns false
$validator->isValid('ewt'); // returns true
$validator->isValid('Foobar');  // returns false
```

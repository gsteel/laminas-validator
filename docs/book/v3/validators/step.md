# Step Validator

`Laminas\Validator\Step` allows you to validate if a given value is a valid step
value. This validator requires the value to be a numeric value (either string,
int or float).

## Supported Options

The following options are supported for `Laminas\Validator\Step`:

- `baseValue`: This is the base value from which the step should be computed.
  This option defaults to `0`
- `step`: This is the step value. This option defaults to `1`

## Basic Usage

```php
$validator = new Laminas\Validator\Step();

if ($validator->isValid(1)) {
    // value is a valid step value
} else {
    // false
}
```

## Using Floating-Point Values

The `Step` validator also supports floating-point base and step values:

```php
$validator = new Laminas\Validator\Step([
    'baseValue' => 1.1,
    'step'      => 2.2,
]);

echo $validator->isValid(1.1); // prints true
echo $validator->isValid(3.3); // prints true
echo $validator->isValid(3.35); // prints false
echo $validator->isValid(2.2); // prints false
```

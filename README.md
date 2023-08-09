<a href="https://skrepr.com/">
  <p align="center">
    <img width="200" height="100" src="https://skrepr.com/theme/skrepr/img/skrepr.svg?a3d5f79941" alt="skrepr" />
  </p>
</a>
<h1 align="center">Data Transfer Object - Tester</h1>

This is a simple library to make unit tests for DTO's and Entity objects easier.
By using this DtoTestCase it will automatically test all public properties and get/set combinations.

Of course this is not just to satisfy the "coverage", but it is to test the basics and makes sure that your DTO's are 
always behave the same as you expect.

Note that you always must create your own extra tests for specific tasks and any business logic.

## Installation

You can install the package using the [Composer](https://getcomposer.org/) package manager. 
It is recommended that you install this only as a development package. 
You can install it by running this command in your project root:

```sh
composer require --dev skrepr/dto-tester
```

## Usage
The abstract class DtoTestCase has 3 required methods for you to implement:
- `getInstance`
- `getTestValuesForProperty`
- `getTestValuesForMethod`

And at this moment one optional setting:
    - $markEmptyAsSkipped

The `getInstance` method should return a testable object. This object is only requested twice during all the tests.
First for the properties and the second time when the methods are tested. 

Next the `getTestValuesForProperty` and `getTestValuesForMethod` methods should return an array with possible values 
that should be tested.

The `getTestValuesForProperty` method gets the property name and type, here you can check and give values you want to test.
You can also give a `null` value instead of an array, then the tests for that property are skipped (unless it is a nullable-property).
The nullable variant will always be tested, it is not possible to use `null` as a value within the array. 

The `getTestValuesForMethod` method works in the same way, it only gives back the method name extra.

For union types, the property/parameter will be tested for every type in the union.

When you don't have special needs, you can use "match" like this:
```php
    protected function getTestValuesForMethod(string $methodName, string $parameterName, string $parameterType): ?array
    {
        return match ($parameterType) {
            'int' => [1, 42, 1337],
            'string' => ['test', 'skrper'],
            'bool' => [true, false],
            SomeClass::class => [new SomeClass(), $this->createMock(SomeClass::class)]
        };
    }
```
It is not recommended to use a "default" when using a match like this. 
In fact, you will get a nice readable exception, so you know what you missed. 


## Known issues
- Readonly properties are not supported
- Not yet possible to test for other result value from getter
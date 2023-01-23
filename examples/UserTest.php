<?php

declare(strict_types=1);

namespace Skrepr\DtoTester\Example;

use Skrepr\DtoTester\DtoTestCase;

class UserTest extends DtoTestCase
{
    protected function getInstance(): mixed
    {
        return new User('name', 'password');
    }

    protected function getTestValuesForProperty(string $propertyName, string $propertyType): ?array
    {
        return match ($propertyName) {
            'name' => ['Michael', 'Erkens'],
            'numberOfLogins' => [0, 42, 1337],
        };
    }

    protected function getTestValuesForMethod(string $methodName, string $parameterName, string $parameterType): ?array
    {
        return ['mail@example.com'];
    }
}

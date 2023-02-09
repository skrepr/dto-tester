<?php

declare(strict_types=1);

namespace Skrepr\DtoTester;

use PHPUnit\Framework\TestCase;

abstract class DtoTestCase extends TestCase
{
    abstract protected function getInstance(): mixed;
    abstract protected function getTestValuesForProperty(string $propertyName, string $propertyType): ?array;
    abstract protected function getTestValuesForMethod(string $methodName, string $parameterName, string $parameterType): ?array;

    /**
     * @deprecated Use $markEmptyAsSkipped
     */
    protected bool $markEmptyAlsSkipped;
    protected bool $markEmptyAsSkipped = true;

    final public function testProperties(): void
    {
        $object = $this->getInstance();
        $reflectionObject = new \ReflectionObject($object);
        $publicProperties = $reflectionObject->getProperties(\ReflectionProperty::IS_PUBLIC);

        if (count($publicProperties) === 0) {
            if (isset($this->markEmptyAlsSkipped)) {
                $this->markEmptyAsSkipped = $this->markEmptyAlsSkipped;
                trigger_error('$markEmptyAlsSkipped is deprecated, use $markEmptyAsSkipped', E_USER_DEPRECATED);
            }

            if ($this->markEmptyAsSkipped) {
                $this->markTestSkipped('No public properties for ' . get_class($this->getInstance()));
            } else {
                $this->assertTrue(true, 'No public properties for ' . get_class($this->getInstance()));
            }
            return;
        }

        foreach ($publicProperties as $property) {
            $type = $property->getType();

            if ($type instanceof \ReflectionNamedType) {
                $this->doTestsForProperty($object, $property, $type);
            } elseif ($type instanceof \ReflectionUnionType) {
                foreach ($type->getTypes() as $namedType) {
                    $this->doTestsForProperty($object, $property, $namedType);
                }
            }
        }
    }

    final public function testGetSetters(): void
    {
        $object = $this->getInstance();
        $reflectionObject = new \ReflectionObject($object);
        $publicMethods = $reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC);

        $publicGetSetters = [];
        foreach ($publicMethods as $method) {
            $result = preg_split('/^(get|set|is)/', $method->getName(), 2, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (!isset($result[1])) {
                continue;
            }
            if (!isset($publicGetSetters[$result[1]])) {
                $publicGetSetters[$result[1]] = [];
            }
            if ($result[0] === 'set') {
                $publicGetSetters[$result[1]]['set'] = $method;
            } else {
                $publicGetSetters[$result[1]]['get'] = $method;
            }
        }

        if (count($publicGetSetters) === 0) {
            if (isset($this->markEmptyAlsSkipped)) {
                $this->markEmptyAsSkipped = $this->markEmptyAlsSkipped;
                trigger_error('$markEmptyAlsSkipped is deprecated, use $markEmptyAsSkipped', E_USER_DEPRECATED);
            }

            if ($this->markEmptyAsSkipped) {
                $this->markTestSkipped('No public getter/setters for ' . get_class($object));
            } else {
                $this->assertTrue(true, 'No public getter/setters for ' . get_class($object));
            }
            return;
        }

        foreach ($publicGetSetters as $propertyName => $getSetter) {
            /** @var \ReflectionMethod[] $getSetter */

            // skip if setters hasn't got 1 parameter or if no getter is found
            if (!isset($getSetter['get'], $getSetter['set']) || $getSetter['set']->getNumberOfParameters() !== 1) {
                continue;
            }
            $parameters = $getSetter['set']->getParameters();
            $type = $parameters[0]->getType();
            if ($type instanceof \ReflectionNamedType) {
                $this->doTestsForGetSetter($object, $getSetter, $parameters[0], $getSetter['set']->getName(), $type);
            } elseif ($type instanceof \ReflectionUnionType) {
                foreach ($type->getTypes() as $namedType) {
                    $this->doTestsForGetSetter($object, $getSetter, $parameters[0], $getSetter['set']->getName(), $namedType);
                }
            }
        }
    }

    private function doTestsForProperty(mixed $object, \ReflectionProperty $property, \ReflectionNamedType $type): void
    {
        $values = $this->getTestValuesForProperty($property->getName(), $type->getName());

        if ($values !== null) {
            foreach ($values as $value) {
                $property->setValue($object, $value);

                $result = $property->getValue($object);
                if (is_object($result)) {
                    self::assertInstanceOf($type->getName(), $result, 'Result type failure for ' . get_class($object) . '::' . $property->getName());
                } else {
                    self::assertEquals($type->getName(), get_debug_type($result), 'Result type failure for ' . get_class($object) . '::' . $property->getName());
                }

                self::assertEquals($value, $result, 'Property failure for ' . get_class($object) . '::' . $property->getName());
            }
        }

        if ($type->allowsNull()) {
            $property->setValue($object, null);
            $result = $property->getValue($object);

            self::assertNull($result, 'Property failure for null-value for ' . get_class($object) . '::' . $property->getName());
        }
    }

    private function doTestsForGetSetter(mixed $object, array $getSetter, \ReflectionParameter $parameter, string $methodName, \ReflectionNamedType $type): void
    {
        $values = $this->getTestValuesForMethod($methodName, $parameter->getName(), $type->getName());

        if ($values !== null) {
            foreach ($values as $value) {
                $getSetter['set']->invoke($object, $value);
                $result = $getSetter['get']->invoke($object);
                self::assertEquals($type->getName(), get_debug_type($result), 'Result type failure for ' . get_class($object) . '::' . $getSetter['get']->getName() . '()');
                self::assertEquals($value, $result, 'Get/Set failure for ' . get_class($object) . '::' . $getSetter['set']->getName() . '()');
            }
        }
        if ($type->allowsNull()) {
            $getSetter['set']->invoke($object, null);
            $result = $getSetter['get']->invoke($object);
            self::assertNull($result, 'Get/Set failure for null-value for' . get_class($object) . '::' . $getSetter['set']->getName() . '()');
        }
    }
}

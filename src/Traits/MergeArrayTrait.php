<?php

/*
 * This file is part of the DmytrofArrayConvertible package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\ArrayConvertible\Traits;

use DateTime;
use DateTimeInterface;
use Dmytrof\ArrayConvertible\Attribute\NestedType;
use Dmytrof\ArrayConvertible\Exception\MergeArrayException;
use Dmytrof\ArrayConvertible\MergeArrayInterface;
use Dmytrof\ArrayConvertible\PrepareMergeArrayValueInterface;
use ReflectionAttribute;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionProperty;
use Throwable;

trait MergeArrayTrait
{
    /**
     * Merges object with array data
     */
    public function mergeArray(array $data): void
    {
        $this->mergeArrayData(\array_diff_key(
            \get_object_vars($this),
            \array_fill_keys($this->getMergeArrayNotSupportedProperties(), true),
        ), $data);
    }

    /**
     * Merges value from array value
     */
    protected function mergeArrayValue(string $property, mixed $value, mixed $dataValue): void
    {
        try {
            $propertyRef = (new ReflectionProperty($this, $property));
            $propertyType = $propertyRef->getType();
        } catch (ReflectionException $e) {
            throw new MergeArrayException(\sprintf(
                'Unable to set \'%s\' property',
                $property,
            ));
        }
        $method = 'set' . \ucfirst($property);
        $setValue = function ($dataValue) use ($property, $value, $method, $propertyRef) {
            if (\is_array($dataValue)) {
                $nestedAttr = $propertyRef->getAttributes(NestedType::class)[0] ?? null;
                if ($nestedAttr instanceof ReflectionAttribute) {
                    $data = [];
                    foreach ($dataValue as $item) {
                        $data[] = $this->mergeArrayCreateObject(
                            $property,
                            $value,
                            $item,
                            $nestedAttr->newInstance()->getClass(),
                            $propertyRef->getType()?->allowsNull() ?? true,
                        );
                    }
                    $dataValue = $data;
                }
            }

            if (\method_exists($this, $method)) {
                $this->$method($dataValue);
            } else {
                $this->$property = $dataValue;
            }
        };

        $typeName = $propertyType?->getName();
        $typeIsNullable = $propertyType?->allowsNull() ?? true;
        if (\in_array($typeName, ['string', 'int', 'float', 'bool', 'array'], true)) { // is scalar or array
            if (!(\is_null($dataValue) && $typeIsNullable)) {
                \settype($dataValue, $typeName);
            }
            $setValue->call($this, $dataValue);

            return;
        }

        try {
            $setValue->call($this, $this->mergeArrayCreateObject(
                $property,
                $value,
                $dataValue,
                $typeName,
                $typeIsNullable,
            ));

            return;
        } catch (MergeArrayException $e) {
        }

        if (\is_null($typeName)) {
            if ($value instanceof MergeArrayInterface) {
                $value->mergeArray($dataValue);
            } else {
                $setValue->call($this, $dataValue);
            }

            return;
        }

        throw new MergeArrayException(\sprintf(
            'Unsupported merge array type \'%s\' for property \'%s\'',
            $typeName,
            $property,
        ));
    }

    protected function mergeArrayCreateObject(
        string $property,
        mixed $value,
        mixed $dataValue,
        ?string $class,
        bool $typeIsNullable,
    ): ?object {
        if (\is_a($class, DateTimeInterface::class, true)) { // date time
            if (!(\is_null($dataValue) && $typeIsNullable)) {
                $dateTimeClass = DateTimeInterface::class === $class ? DateTime::class : $class;

                return new $dateTimeClass($dataValue);
            }

            return null;
        }
        if (\is_a($class, PrepareMergeArrayValueInterface::class, true)) {
            if (!(\is_null($dataValue) && $typeIsNullable)) {
                try {
                    return (new $class())->prepareMergeArrayValue($dataValue);
                } catch (Throwable) {
                    if (!$value instanceof PrepareMergeArrayValueInterface) {
                        throw new MergeArrayException(\sprintf(
                            'Unable to prepare value for \'%s\' property \'%s\' which is not object',
                            PrepareMergeArrayValueInterface::class,
                            $property,
                        ));
                    }

                    return $value->prepareMergeArrayValue($dataValue);
                }
            }

            return null;
        }
        if (\is_a($class, MergeArrayInterface::class, true)) {
            if (
                (!$typeIsNullable || \is_array($dataValue))
                && !$value instanceof MergeArrayInterface
            ) {
                try {
                    $value = new $class();
                } catch (Throwable $e) {
                    throw new MergeArrayException(\sprintf(
                        'Unable to instantiate \'%s\' property \'%s\' with \'%s\' object: %s',
                        MergeArrayInterface::class,
                        $property,
                        $class,
                        $e->getMessage(),
                    ));
                }
            }
            if (\is_array($dataValue) && $value instanceof MergeArrayInterface) {
                $value->mergeArray($dataValue);

                return $value;
            }

            return null;
        }
        throw new MergeArrayException('Unsupported type class');
    }

    /**
     * Merges data from array
     */
    protected function mergeArrayData(array $properties, array $data): void
    {
        foreach ($properties as $property => $value) {
            if (!\array_key_exists($property, $data)) {
                continue;
            }
            $this->mergeArrayValue($property, $value, $data[$property]);
        }
    }

    /**
     * Returns not supported properties
     */
    protected function getMergeArrayNotSupportedProperties(): array
    {
        foreach (['MERGE_ARRAY_NOT_SUPPORTED_PROPERTIES', 'ARRAY_NOT_CONVERTIBLE_PROPERTIES'] as $constant) {
            try {
                return (array) (new ReflectionClassConstant(static::class, $constant))->getValue();
            } catch (ReflectionException) {
            }
        }

        return [];
    }
}

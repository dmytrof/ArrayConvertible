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

use Dmytrof\ArrayConvertible\Exception\MergeArrayException;
use Dmytrof\ArrayConvertible\MergeArrayInterface;
use Dmytrof\ArrayConvertible\PrepareMergeArrayValueInterface;

trait MergeArrayTrait
{
    /**
     * Merges object with array data
     * @param array $data
     *
     * @return void
     */
    public function mergeArray(array $data): void
    {
        $this->mergeArrayData(array_diff_key(
            get_object_vars($this),
            array_fill_keys($this->getMergeArrayNotSupportedProperties(), true),
        ), $data);
    }

    /**
     * Merges value from array value
     * @param string $property
     * @param mixed $value
     * @param mixed $dataValue
     */
    protected function mergeArrayValue(string $property, mixed $value, mixed $dataValue): void
    {
        $method = 'set' . ucfirst($property);
        $setValue = function ($dataValue) use ($property, $method) {
            if (method_exists($this, $method)) {
                $this->$method($dataValue);
            } else {
                $this->$property = $dataValue;
            }
        };
        try {
            $propertyType = (new \ReflectionProperty($this, $property))->getType();
        } catch (\ReflectionException $e) {
            throw new MergeArrayException(sprintf(
                'Unable to set \'%s\' property: %s',
                $property,
                $e->getMessage(),
            ));
        }

        $typeName = $propertyType ? $propertyType->getName() : null;
        if (in_array($typeName, ['string', 'int', 'float', 'bool', 'array'], true)) { // is scalar or array
            if (!(is_null($dataValue) && $propertyType->allowsNull())) {
                settype($dataValue, $typeName);
            }
            $setValue->call($this, $dataValue);

            return;
        }
        if (is_a($typeName, \DateTimeInterface::class, true)) { // date time
            if (!(is_null($dataValue) && $propertyType->allowsNull())) {
                $dataValue = $this->mergeArrayCreateDateTimeObject($property, $value, $dataValue, $typeName);
            }
            $setValue->call($this, $dataValue);

            return;
        }
        if (is_a($typeName, PrepareMergeArrayValueInterface::class, true)) {
            if (!$value instanceof PrepareMergeArrayValueInterface) {
                throw new MergeArrayException(sprintf(
                    'Unable to prepare value for \'%s\' property \'%s\' which is not object',
                    PrepareMergeArrayValueInterface::class,
                    $property,
                ));
            }
            $setValue->call($this, $value->prepareMergeArrayValue($dataValue));

            return;
        }
        if (is_null($typeName) || is_a($typeName, MergeArrayInterface::class, true)) {
            if (!$value instanceof MergeArrayInterface) {
                throw new MergeArrayException(sprintf(
                    'Unable to merge \'%s\' property \'%s\' which is not object',
                    MergeArrayInterface::class,
                    $property,
                ));
            }
            $value->mergeArray($dataValue);

            return;
        }

        throw new MergeArrayException(sprintf(
            'Unsupported merge array type \'%s\'',
            $propertyType->getName(),
        ));
    }

    /**
     * Creates date time object
     * @param string $property
     * @param mixed $value
     * @param mixed $dataValue
     * @param string|null $typeName
     *
     * @return \DateTime|mixed
     */
    protected function mergeArrayCreateDateTimeObject(
        string $property,
        mixed $value,
        mixed $dataValue,
        ?string $typeName
    ): ?\DateTimeInterface {
        $dateTimeClass = $typeName === \DateTimeInterface::class ? \DateTime::class : $typeName;

        return new $dateTimeClass($dataValue);
    }

    /**
     * Merges data from array
     * @param array $properties
     * @param array $data
     */
    protected function mergeArrayData(array $properties, array $data): void
    {
        foreach ($properties as $property => $value) {
            if (!array_key_exists($property, $data)) {
                continue;
            }
            $this->mergeArrayValue($property, $value, $data[$property]);
        }
    }

    /**
     * Returns not supported properties
     * @return array|string[]
     */
    protected function getMergeArrayNotSupportedProperties(): array
    {
        foreach (['MERGE_ARRAY_NOT_SUPPORTED_PROPERTIES', 'ARRAY_NOT_CONVERTIBLE_PROPERTIES'] as $constant) {
            try {
                return (array) (new \ReflectionClassConstant(static::class, $constant))->getValue();
            } catch (\ReflectionException $e) {
            }
        }

        return [];
    }
}

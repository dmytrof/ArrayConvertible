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

use Dmytrof\ArrayConvertible\Exception\FromArrayConvertibleException;
use Dmytrof\ArrayConvertible\FromArrayConvertibleInterface;

trait FromArrayConvertibleTrait
{
    /**
     * Merges object with array data
     * @param array $data
     *
     * @return void
     */
    public function fromArray(array $data): void
    {
        $this->convertFromArrayData(array_diff_key(get_object_vars($this), array_fill_keys($this->getFromArrayNotConvertibleProperties(), true)), $data);
    }

    /**
     * Sets from array value
     * @param string $property
     * @param $value
     * @param $dataValue
     */
    protected function convertFromArrayValue(string $property, $value, $dataValue): void
    {
        $method = 'set'.ucfirst($property);
        $setValue = function ($dataValue) use ($property, $method) {
            var_dump($property, $dataValue);
            if (method_exists($this, $method)) {
                $this->$method($dataValue);
            } else {
                $this->$property = $dataValue;
            }
        };
        try {
            $propertyType = (new \ReflectionProperty($this, $property))->getType();
        } catch (\ReflectionException $e) {
            throw new FromArrayConvertibleException(sprintf('Unable to set \'%s\' property: %s', $property, $e->getMessage()));
        }

        if (in_array($propertyType->getName(), ['string', 'int', 'float', 'bool'], true)) { // is scalar
            if (!(is_null($dataValue) && $propertyType->allowsNull())) {
                settype($dataValue, $propertyType->getName());
            }
            $setValue->call($this, $dataValue);

            return;
        }
        if ($propertyType->getName() === 'array') { // is array
            $setValue->call($this, array_merge((array) $value, (array) $dataValue));

            return;
        }
        if (is_subclass_of($propertyType->getName(), FromArrayConvertibleInterface::class)) {
            if (!$value instanceof FromArrayConvertibleInterface) {
                throw new FromArrayConvertibleException(sprintf('Unable to set from array \'%s\' property \'%s\' which is not object', FromArrayConvertibleInterface::class, $property));
            }
            $value->fromArray($dataValue);

            return;
        }

        throw new FromArrayConvertibleException(sprintf('Unsupported array convertible type \'%s\'', $propertyType->getName()));
    }

    /**
     * Converts from array data
     * @param $properties
     * @param array $data
     */
    protected function convertFromArrayData($properties, array $data): void
    {
        foreach ($properties as $property => $value) {
            if (!array_key_exists($property, $data)) {
                continue;
            }
            $this->convertFromArrayValue($property, $value, $data[$property]);
        }
    }

    /**
     * Returns not convertible properties
     * @return array|string[]
     */
    protected function getFromArrayNotConvertibleProperties(): array
    {
        foreach (['FROM_ARRAY_NOT_CONVERTIBLE_PROPERTIES', 'ARRAY_NOT_CONVERTIBLE_PROPERTIES'] as $constant) {
            try {
                return (array) (new \ReflectionClassConstant(static::class, $constant))->getValue();
            } catch (\ReflectionException $e) {
            }
        }

        return [];
    }
}

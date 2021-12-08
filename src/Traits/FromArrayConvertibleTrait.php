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
     * @return FromArrayConvertibleInterface
     */
    public function fromArray(array $data): FromArrayConvertibleInterface
    {
        $this->convertFromArrayData(array_diff_key(get_object_vars($this), array_fill_keys($this->getFromArrayNotConvertibleProperties(), true)), $data);

        return $this;
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
        try {
            $propertyType = (new \ReflectionProperty($this, $property))->getType();
        } catch (\ReflectionException $e) {
            throw new FromArrayConvertibleException(sprintf('Unable to set \'%s\' property: %s', $property, $e->getMessage()));
        }

        if (in_array($propertyType->getName(), ['string', 'int', 'float', 'bool'])) { // is scalar
            if (!(is_null($dataValue) && $propertyType->allowsNull())) {
                settype($dataValue, $propertyType->getName());
            }
            $this->$method($dataValue);
        } elseif ($propertyType->getName() === 'array') { // is array
            $this->$method(array_merge((array) $value, (array) $dataValue));
        } elseif (is_subclass_of($propertyType->getName(), FromArrayConvertibleInterface::class)) {
            if (!$value instanceof FromArrayConvertibleInterface) {
                throw new FromArrayConvertibleException(sprintf('Unable to set from array \'%s\' property \'%s\' which is not object', FromArrayConvertibleInterface::class, $property));
            }
            $value->fromArray($dataValue);
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
            if (!isset($data[$property])) {
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

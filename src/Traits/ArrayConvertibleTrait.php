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

use Dmytrof\ArrayConvertible\ArrayConvertibleInterface;
use Dmytrof\ArrayConvertible\Exception\ArrayConvertibleException;

trait ArrayConvertibleTrait
{
    /**
     * Converts configuration to array
     * @return array
     */
    public function toArray(): array
    {
        return $this->convertToArrayData(array_diff_key(get_object_vars($this), array_fill_keys($this->getNotArrayConvertibleProperties(), true)));
    }

    /**
     * Returns value converted for toArray
     * @param $value
     *
     * @return array|bool|float|int|string|null
     */
    protected function convertToArrayValue($value)
    {
        if (is_scalar($value)) {
            return $value;
        }
        if (is_null($value)) {
            return null;
        }
        if (is_array($value)) {
            return $this->convertToArrayData($value);
        }
        if ($value instanceof ArrayConvertibleInterface) {
            return $value->toArray();
        }

        throw new ArrayConvertibleException(sprintf('Unsupported array convertible type \'%s\'', is_object($value) ? get_class($value) : gettype($value)));
    }

    /**
     * Converts data to array
     * @param array $data
     *
     * @return array
     */
    protected function convertToArrayData(array $data): array
    {
        $array = [];
        foreach ($data as $property => $value) {
            $array[$property] = $this->convertToArrayValue($value);
        }

        return $array;
    }

    /**
     * Returns not convertible properties
     * @return array|string[]
     */
    protected function getNotArrayConvertibleProperties(): array
    {
        try {
            $constant_reflex = new \ReflectionClassConstant(static::class, 'ARRAY_NOT_CONVERTIBLE_PROPERTIES');

            return (array) $constant_reflex->getValue();
        } catch (\ReflectionException $e) {
            return [];
        }
    }
}

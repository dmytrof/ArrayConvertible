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

use DateTimeInterface;
use Dmytrof\ArrayConvertible\Exception\ToArrayConvertibleException;
use Dmytrof\ArrayConvertible\ToArrayConvertibleInterface;
use Dmytrof\ArrayConvertible\ToArrayValueConvertibleInterface;
use ReflectionClassConstant;
use ReflectionException;

trait ToArrayConvertibleTrait
{
    /**
     * Converts object to array
     */
    public function toArray(): array
    {
        return $this->convertToArrayData(\array_diff_key(
            \get_object_vars($this),
            \array_fill_keys($this->getToArrayNotConvertibleProperties(), true),
        ));
    }

    /**
     * Returns converted value
     */
    protected function convertToArrayValue(mixed $value, ?string $property = null): mixed
    {
        if (\is_scalar($value)) {
            return $value;
        }
        if (\is_null($value)) {
            return null;
        }
        if (\is_array($value)) {
            return $this->convertToArrayData($value, $property);
        }
        if ($value instanceof DateTimeInterface) {
            return $this->convertToArrayDateTime($value, $property);
        }
        if ($value instanceof ToArrayConvertibleInterface) {
            return $value->toArray();
        }
        if ($value instanceof ToArrayValueConvertibleInterface) {
            return $value->toArrayValue();
        }

        throw new ToArrayConvertibleException(\sprintf(
            'Unsupported array convertible type \'%s\' for property \'%s\'',
            \is_object($value) ? \get_class($value) : \gettype($value),
            $property,
        ));
    }

    /**
     * Converts date time to string
     */
    protected function convertToArrayDateTime(DateTimeInterface $value, ?string $property = null): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }

    /**
     * Converts array data to array
     */
    protected function convertToArrayData(array $data, ?string $property = null): array
    {
        $array = [];
        foreach ($data as $prop => $value) {
            $array[$prop] = $this->convertToArrayValue($value, \ltrim($property . '.' . $prop, '.'));
        }

        return $array;
    }

    /**
     * Returns not convertible properties
     */
    protected function getToArrayNotConvertibleProperties(): array
    {
        foreach (['TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES', 'ARRAY_NOT_CONVERTIBLE_PROPERTIES'] as $constant) {
            try {
                return (array) (new ReflectionClassConstant(static::class, $constant))->getValue();
            } catch (ReflectionException) {
            }
        }

        return [];
    }
}

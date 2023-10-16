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

use BackedEnum;
use Dmytrof\ArrayConvertible\Exception\ArrayConvertibleException;

trait EnumToArrayValueConvertibleTrait
{
    /**
     * Converts object to array value
     * @return mixed
     */
    public function toArrayValue(): mixed
    {
        $this->checkBackedEnum();

        return $this->value;
    }

    /**
     * Prepares merge array value to set to object
     * @param mixed $value
     *
     * @return mixed
     */
    public function prepareMergeArrayValue(mixed $value): mixed
    {
        $this->checkBackedEnum();
        if (!is_string($value) && !is_int($value)) {
            throw new ArrayConvertibleException(
                sprintf(
                    'Unsupported value type %s for %s::from method',
                    get_debug_type($value),
                    self::class,
                )
            );
        }

        return self::from($value);
    }

    /**
     * Checks backed enum
     * @return bool
     */
    private function checkBackedEnum(): bool
    {
        if (!$this instanceof BackedEnum) {
            throw new ArrayConvertibleException(
                sprintf(
                    'Trait should be included to %s enum. Currently it is included to %s',
                    BackedEnum::class,
                    get_debug_type($this),
                )
            );
        }

        return true;
    }
}

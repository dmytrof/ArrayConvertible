<?php

/*
 * This file is part of the DmytrofArrayConvertible package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\ArrayConvertible;

interface PrepareMergeArrayValueInterface
{
    /**
     * Prepares merge array value to set to object
     * @param scalar $value
     *
     * @return mixed
     */
    public function prepareMergeArrayValue($value);
}

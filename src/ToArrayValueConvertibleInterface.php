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

interface ToArrayValueConvertibleInterface
{
    /**
     * Converts object to array value
     * @return mixed
     */
    public function toArrayValue();
}

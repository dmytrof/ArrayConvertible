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

interface FromArrayConvertibleInterface
{
    /**
     * Merges array data to object
     * @param array $data
     */
    public function fromArray(array $data): void;
}

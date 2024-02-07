<?php

/*
 * This file is part of the DmytrofArrayConvertible package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\ArrayConvertible\Attribute;

use Dmytrof\ArrayConvertible\Exception\MergeArrayException;
use Dmytrof\ArrayConvertible\MergeArrayInterface;
use Dmytrof\ArrayConvertible\PrepareMergeArrayValueInterface;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
class NestedType
{
    public function __construct(
        private readonly string $class,
    ) {
        if (
            !\is_a($this->class, MergeArrayInterface::class, true)
            && !\is_a($this->class, PrepareMergeArrayValueInterface::class, true)
        ) {
            throw new MergeArrayException(\sprintf(
                'Class %s is not instance of %s or %s',
                $this->class,
                MergeArrayInterface::class,
                PrepareMergeArrayValueInterface::class,
            ));
        }
    }

    public function getClass(): string
    {
        return $this->class;
    }
}

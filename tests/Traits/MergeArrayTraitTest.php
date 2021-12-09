<?php

/*
 * This file is part of the DmytrofArrayConvertible package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\ArrayConvertible\Tests\Traits;

use Dmytrof\ArrayConvertible\MergeArrayInterface;
use Dmytrof\ArrayConvertible\Traits\MergeArrayTrait;
use PHPUnit\Framework\TestCase;

class MergeArrayTraitTest extends TestCase
{
    public function testMergeArray(): void
    {
        $object = new class implements MergeArrayInterface
        {
            use MergeArrayTrait ;

            public int $foo = 1;
            protected ?string $bar = 'bar';
            private array $baz = [
                'hello' => 'world',
                'test' => 'pest',
            ];

            public function getAllVars(): array
            {
                return get_object_vars($this);
            }
        };

        $object->mergeArray([
            'foo' => '12',
            'bar' => null,
            'baz' => [
                'hello' => 'mello',
                'woo' => 'hoo',
            ],
        ]);

        $this->assertEquals([
            'foo' => 12,
            'bar' => null,
            'baz' => [
                'hello' => 'mello',
                'test' => 'pest',
                'woo' => 'hoo',
            ],
        ], $object->getAllVars());

        $nestedObject = new class implements MergeArrayInterface
        {
            use MergeArrayTrait;

            private const ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['notConvertibleProperty'];

            public int $nested1 = 1;
            protected string $nested2 = 'bar';
            private ?string $nested3 = null;
            private string $notConvertibleProperty = 'qwe'; // Must be avoided in mergeArray and toArray

            public function __construct()
            {
                $this->closure = function() {
                    return 55;
                };
            }

            public function getAllVars(): array
            {
                return get_object_vars($this);
            }
        };


        $object = new class ($nestedObject) implements MergeArrayInterface
        {
            use MergeArrayTrait;

            private const MERGE_ARRAY_NOT_SUPPORTED_PROPERTIES = ['notConvertibleProperty'];

            public int $foo = 1;
            protected ?string $bar = 'bar';
            private array $baz = [
                'hello' => 'world',
                4,
                null,
            ];
            private bool $notConvertibleProperty = false; // Must be avoided in mergeArray
            private $nestedObject;

            public function __construct($nestedObject)
            {
                $this->nestedObject = $nestedObject;
            }

            public function getAllVars(): array
            {
                return get_object_vars($this);
            }
        };


        $object->mergeArray([
            'foo' => '12',
            'bar' => null,
            'baz' => [
                'hello' => 'mello',
                'woo' => 'hoo',
            ],
            'notConvertibleProperty' => 'not usable',
            'nestedObject' => [
                'nested1' => 11,
                'nested2' => 22,
                'nested3' => 33,
                'notConvertibleProperty' => '44',
            ],
        ]);

        $this->assertEquals([
            'foo' => 12,
            'bar' => null,
            'baz' => [
                'hello' => 'mello',
                'test' => 'pest',
                'woo' => 'hoo',
            ],
            'notConvertibleProperty' => false,
            'nestedObject' => [
                'nested1' => 11,
                'nested2' => '22',
                'nested3' => '33',
            ],
        ], $object->getAllVars());
    }
//
//    public function testGetNotArrayConvertibleProperties(): void
//    {
//        $objectWithoutConst = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait {
//                getToArrayNotConvertibleProperties AS public;
//            }
//        };
//
//        $object1 = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait {
//                getToArrayNotConvertibleProperties AS public;
//            }
//
//            protected const ARRAY_NOT_CONVERTIBLE_PROPERTIES = 'test';
//        };
//
//        $object2 = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait {
//                getToArrayNotConvertibleProperties AS public;
//            }
//
//            protected const TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['foo', 'bar'];
//        };
//
//        $this->assertEquals([], $objectWithoutConst->getToArrayNotConvertibleProperties());
//        $this->assertEquals(['test'], $object1->getToArrayNotConvertibleProperties());
//        $this->assertEquals(['foo', 'bar'], $object2->getToArrayNotConvertibleProperties());
//    }

}

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

use Dmytrof\ArrayConvertible\FromArrayConvertibleInterface;
use Dmytrof\ArrayConvertible\Traits\FromArrayConvertibleTrait;
use PHPUnit\Framework\TestCase;

class FromArrayConvertibleTraitTest extends TestCase
{
    public function testConvertToArrayValue(): void
    {
        $object = new class implements FromArrayConvertibleInterface
        {
            use FromArrayConvertibleTrait ;

            public int $foo = 1;
            protected ?string $bar = 'bar';
            private array $baz = [
                'hello' => 'world',
                4,
                null,
            ];

            public function getAllVars(): array
            {
                return get_object_vars($this);
            }
        };

        $object->fromArray([
            'foo' => '12',
            'bar' => null,
            'baz' => [
                'hello' => 'mello',
                7,
                'not null',
                'woo' => 'hoo',
            ],
        ]);

        $this->assertEquals([
            'foo' => 12,
            'bar' => null,
            'baz' => [
                'hello' => 'mello',
                7,
                'not null',
                'woo' => 'hoo',
            ],
        ], $object->getAllVars());


//        $object = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait {
//                convertToArrayValue as private __convertToArrayValue;
//            }
//
//            private const TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['notConvertibleProperty'];
//
//            public $foo = 1;
//            protected $bar = 'bar';
//            private $baz = [
//                'hello' => 'world',
//                4,
//                null,
//            ];
//            private $closure;
//            private $notConvertibleProperty; // Must be avoided in toArray
//
//            public function __construct()
//            {
//                $this->closure = function() {
//                    return 55;
//                };
//            }
//
//            public function convertToArrayValue($value)
//            {
//                try {
//                    return $this->__convertToArrayValue($value);
//                } catch (ToArrayConvertibleException $e) {
//                    if ($value instanceof \Closure) {
//                        return $value->call($this);
//                    }
//                    throw $e;
//                }
//            }
//        };
//
//
//        $this->assertEquals([
//            'foo' => 1,
//            'bar' => 'bar',
//            'baz' => [
//                'hello' => 'world',
//                4,
//                null,
//            ],
//            'closure' => 55,
//        ], $object->convertToArrayValue($object));
    }

//    public function testConvertToArrayData(): void
//    {
//        $object = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait {
//                convertToArrayData AS public;
//            }
//            public $foo = 1;
//            protected $bar = 'bar';
//            private $baz = [
//                'hello' => 'world',
//                4,
//                null,
//            ];
//        };
//
//        $this->assertEquals([], $object->convertToArrayData([]));
//        $this->assertEquals([[]], $object->convertToArrayData([[]]));
//        $this->assertEquals(['test' => 'test'], $object->convertToArrayData(['test' => 'test']));
//        $this->assertEquals([1,3,5], $object->convertToArrayData([1,3,5]));
//        $this->assertEquals(['foo' => 'bar', 3, null], $object->convertToArrayData(['foo' => 'bar', 3, null]));
//        $this->assertEquals(['foo' => 'bar', 3, [['baz' => true]]], $object->convertToArrayData(['foo' => 'bar', 3, [['baz' => true]]]));
//        $this->assertEquals([
//            'foo' => 'bar',
//            3,
//            null,
//            'object' => [
//                'foo' => 1,
//                'bar' => 'bar',
//                'baz' => [
//                    'hello' => 'world',
//                    4,
//                    null,
//                ],
//            ],
//        ], $object->convertToArrayData(['foo' => 'bar', 3, null, 'object' => $object]));
//    }
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
//
//    public function testToArray(): void
//    {
//        $objectWithoutConst = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait;
//
//            public $foo = 1;
//            protected $bar = 'bar';
//            private $baz = [
//                'hello' => 'world',
//                4,
//                null,
//            ];
//        };
//
//        $object1 = new class implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait;
//
//            protected const TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES = 'foo';
//
//            public $foo = 1;
//            protected $bar = 'bar';
//            private $baz = [
//                'hello' => 'world',
//                4,
//                null,
//            ];
//        };
//
//        $object2 = new class ($objectWithoutConst, $object1) implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait;
//
//            protected const TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['foo', 'bar'];
//
//            public $foo = 1;
//            protected $bar = 'bar';
//            protected $objectWithoutConst;
//            private $baz = [
//                'hello' => 'world',
//                4,
//                null,
//            ];
//            private $object1;
//
//            public function __construct($objectWithoutConst, $object1)
//            {
//                $this->objectWithoutConst = $objectWithoutConst;
//                $this->object1 = $object1;
//            }
//        };
//
//        $object3 = new class ($objectWithoutConst, $object1) implements ToArrayConvertibleInterface
//        {
//            use ToArrayConvertibleTrait;
//
//            protected const TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['foo', 'bar'];
//
//            public $foo = 1;
//            protected $bar = 'bar';
//            protected $objectWithoutConst;
//            private $baz = [
//                'hello' => 'world',
//                4,
//                null,
//            ];
//            private $object1;
//            private $stdClass;
//
//            public function __construct($objectWithoutConst, $object1)
//            {
//                $this->objectWithoutConst = $objectWithoutConst;
//                $this->object1 = $object1;
//                $this->stdClass = new \stdClass();
//            }
//        };
//
//        $this->assertEquals([
//            'foo' => 1,
//            'bar' => 'bar',
//            'baz' => [
//                'hello' => 'world',
//                4,
//                null,
//            ],
//        ], $objectWithoutConst->toArray());
//
//        $this->assertEquals([
//            'bar' => 'bar',
//            'baz' => [
//                'hello' => 'world',
//                4,
//                null,
//            ],
//        ], $object1->toArray());
//
//        $this->assertEquals([
//            'baz' => [
//                'hello' => 'world',
//                4,
//                null,
//            ],
//            'objectWithoutConst' => [
//                'foo' => 1,
//                'bar' => 'bar',
//                'baz' => [
//                    'hello' => 'world',
//                    4,
//                    null,
//                ],
//            ],
//            'object1' => [
//                'bar' => 'bar',
//                'baz' => [
//                    'hello' => 'world',
//                    4,
//                    null,
//                ],
//            ],
//        ], $object2->toArray());
//
//        $this->expectException(ToArrayConvertibleException::class);
//        $object3->toArray();
//    }
}

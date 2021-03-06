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
use Dmytrof\ArrayConvertible\ToArrayConvertibleInterface;
use Dmytrof\ArrayConvertible\Traits\MergeArrayTrait;
use Dmytrof\ArrayConvertible\Traits\ToArrayConvertibleTrait;
use PHPUnit\Framework\TestCase;

class MergeArrayTraitTest extends TestCase
{
    public function testMergeArray(): void
    {
        $object = new class implements MergeArrayInterface
        {
            use MergeArrayTrait {
                mergeArrayCreateDateTimeObject AS _mergeArrayCreateDateTimeObject;
            }

            public int $foo = 1;
            protected ?string $bar = 'bar';
            protected ?\DateTimeInterface $nullDate = null;
            protected \DateTimeInterface $date;
            protected \DateTimeImmutable $immutableDate;
            private array $baz = [
                'hello' => 'world',
                'test' => 'pest',
            ];
            public function __construct()
            {
                $this->date = new \DateTime('2022-01-22T22:22:22+00:00');
                $this->immutableDate = new \DateTimeImmutable('2021-01-01T00:00:00+00:00');
            }
            public function getAllVars(): array
            {
                return get_object_vars($this);
            }
            protected function mergeArrayCreateDateTimeObject(string $property, $value, $dataValue, ?string $typeName): ?\DateTimeInterface
            {
                if ($property === 'immutableDate') {
                    return new \DateTimeImmutable($dataValue);
                }

                return $this->_mergeArrayCreateDateTimeObject($property, $value, $dataValue, $typeName);
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
            'nullDate' => null,
            'date' => new \DateTime('2022-01-22T22:22:22+00:00'),
            'immutableDate' => new \DateTimeImmutable('2021-01-01T00:00:00+00:00'),
            'baz' => [
                'hello' => 'mello',
                'woo' => 'hoo',
            ],
        ], $object->getAllVars());

        $object->mergeArray([
            'nullDate' => null,
            'date' => '2000-01-22',
            'immutableDate' => '2022-01-01T01:01:01+03:00',
        ]);

        $this->assertEquals([
            'foo' => 12,
            'bar' => null,
            'nullDate' => null,
            'date' => new \DateTime('2000-01-22'),
            'immutableDate' => new \DateTimeImmutable('2022-01-01T01:01:01+03:00'),
            'baz' => [
                'hello' => 'mello',
                'woo' => 'hoo',
            ],
        ], $object->getAllVars());

        $nestedObject = new class implements MergeArrayInterface, ToArrayConvertibleInterface
        {
            use MergeArrayTrait;
            use ToArrayConvertibleTrait;

            private const ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['notConvertibleProperty'];

            public int $nested1 = 1;
            protected string $nested2 = 'bar';
            private ?string $nested3 = null;
            private string $notConvertibleProperty = 'qwe'; // Must be avoided in mergeArray and toArray
        };

        $object = new class ($nestedObject) implements MergeArrayInterface
        {
            use MergeArrayTrait;

            private const MERGE_ARRAY_NOT_SUPPORTED_PROPERTIES = ['notConvertibleProperty'];

            public int $foo = 1;
            protected ?string $bar = 'bar';
            private array $baz = [
                'hello' => 'world',
                'test' => 'pest',
            ];
            private bool $notConvertibleProperty = false; // Must be avoided in mergeArray
            private $nestedObject;

            public function __construct($nestedObject)
            {
                $this->nestedObject = $nestedObject;
            }

            public function getAllVars(): array
            {
                return array_merge(get_object_vars($this), ['nestedObject' => $this->nestedObject->toArray()]);
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

    public function testGetNotArrayConvertibleProperties(): void
    {
        $objectWithoutConst = new class implements MergeArrayInterface
        {
            use MergeArrayTrait {
                getMergeArrayNotSupportedProperties AS public;
            }
        };

        $object1 = new class implements MergeArrayInterface
        {
            use MergeArrayTrait {
                getMergeArrayNotSupportedProperties AS public;
            }

            protected const ARRAY_NOT_CONVERTIBLE_PROPERTIES = 'test';
        };

        $object2 = new class implements MergeArrayInterface
        {
            use MergeArrayTrait {
                getMergeArrayNotSupportedProperties AS public;
            }

            protected const MERGE_ARRAY_NOT_SUPPORTED_PROPERTIES = ['foo', 'bar'];
        };

        $this->assertEquals([], $objectWithoutConst->getMergeArrayNotSupportedProperties());
        $this->assertEquals(['test'], $object1->getMergeArrayNotSupportedProperties());
        $this->assertEquals(['foo', 'bar'], $object2->getMergeArrayNotSupportedProperties());
    }
}

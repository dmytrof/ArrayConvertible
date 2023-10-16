<?php

namespace Dmytrof\ArrayConvertible\Tests\Traits;

use Dmytrof\ArrayConvertible\EnumToArrayValueConvertibleInterface;
use Dmytrof\ArrayConvertible\Exception\ArrayConvertibleException;
use Dmytrof\ArrayConvertible\Traits\EnumToArrayValueConvertibleTrait;
use PHPUnit\Framework\TestCase;

class EnumToArrayValueConvertibleTraitTest extends TestCase
{
    public function testMergeArray(): void
    {
        $enum = IntType::ONE;
        $this->assertEquals($enum->value, $enum->toArrayValue());
        $enum = IntType::TWO;
        $this->assertEquals($enum->value, $enum->toArrayValue());
        $enum = IntType::THREE;
        $this->assertEquals($enum->value, $enum->toArrayValue());

        $enum = StringType::FOO;
        $this->assertEquals($enum->value, $enum->toArrayValue());
        $enum = StringType::BAR;
        $this->assertEquals($enum->value, $enum->toArrayValue());
        $enum = StringType::BAZ;
        $this->assertEquals($enum->value, $enum->toArrayValue());

        $enum = NotBackedEnum::ONE;
        $this->expectException(ArrayConvertibleException::class);
        $enum->toArrayValue();
    }

    public function testPrepareMergeArrayValue(): void
    {
        $enum = IntType::ONE;
        $this->assertEquals($enum, $enum->prepareMergeArrayValue($enum->value));
        $enum = IntType::TWO;
        $this->assertEquals($enum, $enum->prepareMergeArrayValue($enum->value));
        $enum = IntType::THREE;
        $this->assertEquals($enum, $enum->prepareMergeArrayValue($enum->value));

        $enum = StringType::FOO;
        $this->assertEquals($enum, $enum->prepareMergeArrayValue($enum->value));
        $enum = StringType::BAR;
        $this->assertEquals($enum, $enum->prepareMergeArrayValue($enum->value));
        $enum = StringType::BAZ;
        $this->assertEquals($enum, $enum->prepareMergeArrayValue($enum->value));

        $enum = NotBackedEnum::ONE;
        $this->expectException(ArrayConvertibleException::class);
        $enum->prepareMergeArrayValue(1);
    }
}

enum IntType: int implements EnumToArrayValueConvertibleInterface
{
    use EnumToArrayValueConvertibleTrait;

    case ONE = 1;
    case TWO = 2;
    case THREE = 3;
}

enum StringType: string implements EnumToArrayValueConvertibleInterface
{
    use EnumToArrayValueConvertibleTrait;

    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}

enum NotBackedEnum implements EnumToArrayValueConvertibleInterface
{
    use EnumToArrayValueConvertibleTrait;

    case ONE;
    case TWO;
    case THREE;
}

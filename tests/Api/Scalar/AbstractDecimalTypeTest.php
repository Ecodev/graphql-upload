<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api\Scalar;

use Ecodev\Felix\Api\Scalar\AbstractDecimalType;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;
use PHPUnit\Framework\TestCase;

final class AbstractDecimalTypeTest extends TestCase
{
    private function createType(int $decimal, ?string $minimum, ?string $maximum): AbstractDecimalType
    {
        return new class($decimal, $minimum, $maximum) extends AbstractDecimalType {
            public $name = 'TestDecimal';

            /**
             * @var int
             */
            private $decimal;

            /**
             * @var null|string
             */
            private $minimum;

            /**
             * @var null|string
             */
            private $maximum;

            public function __construct(int $decimal, ?string $minimum, ?string $maximum)
            {
                parent::__construct([]);
                $this->decimal = $decimal;
                $this->minimum = $minimum;
                $this->maximum = $maximum;
            }

            protected function getScale(): int
            {
                return $this->decimal;
            }

            protected function getMinimum(): ?string
            {
                return $this->minimum;
            }

            protected function getMaximum(): ?string
            {
                return $this->maximum;
            }
        };
    }

    /**
     * @dataProvider providerInputs
     *
     * @param null|float|int|string $input
     */
    public function testSerialize(int $decimal, ?string $minimum, ?string $maximum, $input, ?string $expected): void
    {
        $type = $this->createType($decimal, $minimum, $maximum);
        $actual = $type->serialize($input);
        self::assertSame($input, $actual);
    }

    /**
     * @dataProvider providerInputs
     *
     * @param null|float|int|string $input
     */
    public function testParseValue(int $decimal, ?string $minimum, ?string $maximum, $input, ?string $expected): void
    {
        $type = $this->createType($decimal, $minimum, $maximum);

        if ($expected === null) {
            $this->expectExceptionMessage('Query error: Not a valid TestDecimal' . ': ' . Utils::printSafe($input));
        }

        $actual = $type->parseValue($input);

        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerInputs
     *
     * @param null|float|int|string $input
     */
    public function testParseLiteral(int $decimal, ?string $minimum, ?string $maximum, $input, ?string $expected): void
    {
        $type = $this->createType($decimal, $minimum, $maximum);

        if (is_string($input)) {
            $ast = new StringValueNode(['value' => $input]);
        } elseif (is_float($input)) {
            $ast = new FloatValueNode(['value' => $input]);
        } else {
            $ast = new IntValueNode(['value' => $input]);
        }

        if ($expected === null) {
            $this->expectExceptionMessage('Query error: Not a valid TestDecimal');
        }

        $actual = $type->parseLiteral($ast);

        self::assertSame($expected, $actual);
    }

    public function providerInputs(): array
    {
        return [
            [3, null, null, null, null],
            [3, null, null, '', null],
            [3, null, null, ' ', null],
            [3, null, null, '0', '0'],
            [3, null, null, '2', '2'],
            [3, null, null, '0.1', '0.1'],
            [3, null, null, '0.12', '0.12'],
            [3, null, null, '0.123', '0.123'],
            [3, null, null, '0.1234', null],
            [3, null, null, '-0', '-0'],
            [3, null, null, '-0.123', '-0.123'],
            [3, null, null, '-0.1234', null],
            [3, null, null, 0, '0'],
            [3, null, null, 2, '2'],
            [3, null, null, 0.1, '0.1'],
            [3, null, null, 0.12, '0.12'],
            [3, null, null, 0.123, '0.123'],
            [3, null, null, 0.1234, null],
            [3, null, null, -0, '0'],
            [3, null, null, -0.123, '-0.123'],
            [3, null, null, -0.1234, null],
            [0, null, null, '0', '0'],
            [0, null, null, '1', '1'],
            [0, null, null, '1.1', null],
            [0, null, null, '-1', '-1'],
            [0, null, null, 0, '0'],
            [0, null, null, 1, '1'],
            [0, null, null, 1.1, null],
            [0, null, null, -1, '-1'],
            [2, '0.00', '1.00', '-0.01', null],
            [2, '0.00', '1.00', '0.00', '0.00'],
            [2, '0.00', '1.00', '1.00', '1.00'],
            [2, '0.00', '1.00', '1.01', null],
            [2, '0.00', '1.00', '0.000', null],
        ];
    }
}

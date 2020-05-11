<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api\Scalar;

use Ecodev\Felix\Api\Scalar\ColorType;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;

final class ColorTypeTest extends TestCase
{
    /**
     * @dataProvider providerColors
     *
     * @param null|string $input
     * @param bool $isValid
     */
    public function testSerialize(?string $input, bool $isValid): void
    {
        $type = new ColorType();
        $actual = $type->serialize($input);
        self::assertSame($input, $actual);
    }

    /**
     * @dataProvider providerColors
     *
     * @param null|string $input
     * @param bool $isValid
     */
    public function testParseValue(?string $input, bool $isValid): void
    {
        $type = new ColorType();

        if (!$isValid) {
            $this->expectExceptionMessage('Query error: Not a valid Color');
        }

        $actual = $type->parseValue($input);

        self::assertSame($input, $actual);
    }

    /**
     * @dataProvider providerColors
     *
     * @param null|string $input
     * @param bool $isValid
     */
    public function testParseLiteral(?string $input, bool $isValid): void
    {
        $type = new ColorType();
        $ast = new StringValueNode(['value' => $input]);

        if (!$isValid) {
            $this->expectExceptionMessage('Query error: Not a valid Color');
        }

        $actual = $type->parseLiteral($ast);

        self::assertSame($input, $actual);
    }

    public function providerColors(): array
    {
        return [
            ['', true],
            ['#AABBCC', true],
            ['#AABBC', false],
            ['#AABBCCC', false],
            ['#01aB9F', true],
            ['#ZZZZZZ', false],
            ['AABBCC', false],
            [null, false],
            [' ', false],
        ];
    }
}

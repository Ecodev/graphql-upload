<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api\Scalar;

use Ecodev\Felix\Api\Scalar\NonUniqueEmailType;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;

final class NonUniqueEmailTypeTest extends TestCase
{
    /**
     * @dataProvider providerEmails
     */
    public function testSerialize(?string $input, ?string $expected, bool $isValid): void
    {
        $type = new NonUniqueEmailType();
        $actual = $type->serialize($input);
        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerEmails
     */
    public function testParseValue(?string $input, ?string $expected, bool $isValid): void
    {
        $type = new NonUniqueEmailType();

        if (!$isValid) {
            $this->expectExceptionMessage('Query error: Not a valid NonUniqueEmail');
        }

        $actual = $type->parseValue($input);

        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerEmails
     */
    public function testParseLiteral(?string $input, ?string $expected, bool $isValid): void
    {
        $type = new NonUniqueEmailType();
        $ast = new StringValueNode(['value' => $input]);

        if (!$isValid) {
            $this->expectExceptionMessage('Query error: Not a valid NonUniqueEmail');
        }

        $actual = $type->parseLiteral($ast);

        self::assertSame($expected, $actual);
    }

    public function providerEmails(): array
    {
        return [
            ['john@example.com', 'john@example.com', true],
            ['josé@example.com', 'josé@example.com', true],
            ['josé@example.non-existing-tld', 'josé@example.non-existing-tld', false],
            ['root@localhost', 'root@localhost', false],
            ['foo', 'foo', false],
            [null, null, false],
            ['', '', true],
        ];
    }
}

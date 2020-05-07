<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api\Scalar;

use Ecodev\Felix\Api\Scalar\EmailType;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;

class EmailTypeTest extends TestCase
{
    /**
     * @dataProvider providerEmails
     */
    public function testSerialize(?string $input, ?string $expected, bool $isValid): void
    {
        $type = new EmailType();
        $actual = $type->serialize($input);
        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerEmails
     */
    public function testParseValue(?string $input, ?string $expected, bool $isValid): void
    {
        $type = new EmailType();

        if (!$isValid) {
            $this->expectExceptionMessage('Query error: Not a valid Email');
        }

        $actual = $type->parseValue($input);

        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerEmails
     */
    public function testParseLiteral(?string $input, ?string $expected, bool $isValid): void
    {
        $type = new EmailType();
        $ast = new StringValueNode(['value' => $input]);

        if (!$isValid) {
            $this->expectExceptionMessage('Query error: Not a valid Email');
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
            ['', null, true],
            ['foo', 'foo', false],
            [null, null, true],
        ];
    }
}

<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Acl\Assertion;

use Ecodev\Felix\Acl\Acl;
use Ecodev\Felix\Acl\Assertion\All;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use PHPUnit\Framework\TestCase;

class AllTest extends TestCase
{
    /**
     * @dataProvider providerAssert
     */
    public function testAssert(array $input, bool $expected): void
    {
        $assertions = [];
        foreach ($input as $value) {
            $internalAssertion = $this->createMock(AssertionInterface::class);
            $internalAssertion->expects(self::atMost(1))
                ->method('assert')
                ->willReturn($value);

            $assertions[] = $internalAssertion;
        }

        $assertion = new All(...$assertions);

        $acl = $this->createMock(Acl::class);
        self::assertSame($expected, $assertion->assert($acl));
    }

    public function providerAssert(): array
    {
        return [
            [[], true],
            [[true], true],
            [[true, true], true],
            [[true, false], false],
            [[false, true], false],
            [[false, false], false],
            [[false], false],
        ];
    }
}

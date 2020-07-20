<?php

declare(strict_types=1);

namespace EcodevTests\Felix\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Ecodev\Felix\DBAL\Types\CHFType;
use InvalidArgumentException;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class CHFTypeTest extends TestCase
{
    /**
     * @var CHFType
     */
    private $type;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    protected function setUp(): void
    {
        $this->type = $this->getMockBuilder(CHFType::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->platform = new MySqlPlatform();
    }

    public function testMoney(): void
    {
        self::assertSame('INT', $this->type->getSqlDeclaration(['foo'], $this->platform));

        // Should always return string
        $actualPhp = $this->type->convertToPHPValue(100, $this->platform);
        self::assertInstanceOf(Money::class, $actualPhp);
        self::assertTrue(Money::CHF(100)->equals($actualPhp));

        // Should support null values
        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));

        self::assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }

    public function testConvertToPHPValueThrowsWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->type->convertToPHPValue('foo', $this->platform);
    }

    public function testConvertToDatabaseValueThrowsWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->type->convertToDatabaseValue('foo', $this->platform);
    }
}

<?php

declare(strict_types=1);

namespace EcodevTests\Felix\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Ecodev\Felix\DBAL\Types\EnumType;
use PHPUnit\Framework\TestCase;

class EnumTypeTest extends TestCase
{
    /**
     * @var EnumType
     */
    private $type;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    public function setUp(): void
    {
        $this->type = new class() extends EnumType {
            protected function getPossibleValues(): array
            {
                return ['value1', 'value2'];
            }
        };

        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testEnum(): void
    {
        self::assertSame("ENUM('value1', 'value2')", $this->type->getSqlDeclaration(['foo'], $this->platform));

        // Should always return string
        self::assertSame('value1', $this->type->convertToPHPValue('value1', $this->platform));

        // Should support null values or empty string
        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
        self::assertNull($this->type->convertToPHPValue('', $this->platform));
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        self::assertNull($this->type->convertToDatabaseValue('', $this->platform));

        self::assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }

    public function testConvertToPHPValueThrowsWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->type->convertToPHPValue('foo', $this->platform);
    }

    public function testConvertToDatabaseValueThrowsWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->type->convertToDatabaseValue('foo', $this->platform);
    }

    public function testConvertToPHPValueThrowsWithZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->type->convertToPHPValue(0, $this->platform);
    }

    public function testConvertToDatabaseValueThrowsWithZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->type->convertToDatabaseValue(0, $this->platform);
    }
}

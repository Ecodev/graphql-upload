<?php

declare(strict_types=1);

namespace Ecodev\Felix\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use InvalidArgumentException;
use Money\Money;

abstract class AbstractMoneyType extends IntegerType
{
    abstract protected function createMoney(string $value): Money;

    public function getName()
    {
        return 'Money';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        $val = $this->createMoney((string) $value);

        return $val;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Money) {
            return $value->getAmount();
        }

        if ($value === null) {
            return $value;
        }

        throw new InvalidArgumentException('Cannot convert to dababase value: ' . var_export($value, true));
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace Ecodev\Felix\DBAL\Types;

use Money\Money;

final class EURType extends AbstractMoneyType
{
    public function getName()
    {
        return 'EUR';
    }

    protected function createMoney(string $value): Money
    {
        return Money::EUR($value);
    }
}

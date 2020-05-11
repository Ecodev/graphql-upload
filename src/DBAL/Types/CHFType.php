<?php

declare(strict_types=1);

namespace Ecodev\Felix\DBAL\Types;

use Money\Money;

class CHFType extends AbstractMoneyType
{
    public function getName()
    {
        return 'CHF';
    }

    protected function createMoney(string $value): Money
    {
        return Money::CHF($value);
    }
}

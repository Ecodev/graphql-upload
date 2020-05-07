<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

use Money\Money;

class CHFType extends AbstractMoneyType
{
    /**
     * @var string
     */
    public $description = 'A CHF money amount.';

    protected function createMoney($value): Money
    {
        return Money::CHF($value);
    }
}

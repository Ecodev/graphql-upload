<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

use Money\Money;

final class CHFType extends AbstractMoneyType
{
    /**
     * @var string
     */
    public $description = 'A CHF money amount.';

    protected function createMoney(string $value): Money
    {
        return Money::CHF($value);
    }
}

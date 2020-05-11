<?php

declare(strict_types=1);

namespace Ecodev\Felix;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

abstract class Format
{
    /**
     * Truncate a string and append '…' at the end
     *
     * @param string $string
     * @param int $maxLength
     * @param string $ellipsis the string to indicate truncation happened
     *
     * @return string truncated string
     */
    public static function truncate(string $string, int $maxLength, string $ellipsis = '…'): string
    {
        if (mb_strlen($string) > $maxLength) {
            $string = mb_substr($string, 0, $maxLength - mb_strlen($ellipsis));
            $string .= $ellipsis;
        }

        return $string;
    }

    /**
     * Shortcut to format money
     *
     * @param Money $money
     *
     * @return string
     */
    public static function money(Money $money): string
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($money);
    }
}

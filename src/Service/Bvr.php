<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Exception;
use Money\Money;

/**
 * Class to generate BVR reference number and encoding lines.
 *
 * Typically usage would one of the following:
 *
 * ```php
 * <?php
 *
 * // Provided by your bank
 * $bankAccount = '800876';
 * $postAccount = '01-3456-0';
 *
 * // Your own custom ID to uniquely identify the payment
 * $myId = (string) $user->getId();
 *
 * $referenceNumberToCopyPasteInEBanking = Bvr::getReferenceNumber($bankAccount, $myId);
 *
 * // OR get encoding line
 * $amount = Money::CHF(1995);
 * $encodingLineToCopyPasteInEBanking = Bvr::getEncodingLine($bankAccount, $myId, $postAccount, $amount);
 * ```
 *
 * @see https://www.postfinance.ch/content/dam/pfch/doc/cust/download/inpayslip_isr_man_fr.pdf
 */
final class Bvr
{
    private const TABLE = [
        [0, 9, 4, 6, 8, 2, 7, 1, 3, 5],
        [9, 4, 6, 8, 2, 7, 1, 3, 5, 0],
        [4, 6, 8, 2, 7, 1, 3, 5, 0, 9],
        [6, 8, 2, 7, 1, 3, 5, 0, 9, 4],
        [8, 2, 7, 1, 3, 5, 0, 9, 4, 6],
        [2, 7, 1, 3, 5, 0, 9, 4, 6, 8],
        [7, 1, 3, 5, 0, 9, 4, 6, 8, 2],
        [1, 3, 5, 0, 9, 4, 6, 8, 2, 7],
        [3, 5, 0, 9, 4, 6, 8, 2, 7, 1],
        [5, 0, 9, 4, 6, 8, 2, 7, 1, 3],
    ];

    /**
     * Get the reference number, including the verification digit
     */
    public static function getReferenceNumber(string $bankAccount, string $customId): string
    {
        if (!preg_match('~^\d{0,20}$~', $customId)) {
            throw new Exception('Invalid custom ID. It must be 20 or less digits, but got: `' . $customId . '`');
        }

        if (!preg_match('~^\d{6}$~', $bankAccount)) {
            throw new Exception('Invalid bank account. It must be exactly 6 digits, but got: `' . $bankAccount . '`');
        }
        $value = $bankAccount . self::pad($customId, 20);

        return $value . self::modulo10($value);
    }

    /**
     * Extract the custom ID as string from a valid reference number
     */
    public static function extractCustomId(string $referenceNumber): string
    {
        if (!preg_match('~^\d{27}$~', $referenceNumber)) {
            throw new Exception('Invalid reference number. It must be exactly 27 digits, but got: `' . $referenceNumber . '`');
        }
        $value = mb_substr($referenceNumber, 0, 26);
        $expectedVerificationDigit = (int) mb_substr($referenceNumber, 26, 27);
        $actualVerificationDigit = self::modulo10($value);
        if ($expectedVerificationDigit !== $actualVerificationDigit) {
            throw new Exception('Invalid reference number. The verification digit does not match. Expected `' . $expectedVerificationDigit . '`, but got `' . $actualVerificationDigit . '`');
        }

        return mb_substr($referenceNumber, 6, 20);
    }

    /**
     * Get the full encoding line
     */
    public static function getEncodingLine(string $bankAccount, string $customId, string $postAccount, ?Money $amount = null): string
    {
        $type = self::getType($amount);
        $referenceNumber = self::getReferenceNumber($bankAccount, $customId);
        $formattedPostAccount = self::getPostAccount($postAccount);

        $result =
            $type . '>'
            . $referenceNumber . '+ '
            . $formattedPostAccount . '>';

        return $result;
    }

    private static function pad(string $string, int $length): string
    {
        return str_pad($string, $length, '0', STR_PAD_LEFT);
    }

    public static function modulo10(string $number): int
    {
        $report = 0;

        if ($number === '') {
            return $report;
        }

        $digits = mb_str_split($number);
        if ($digits === false) {
            throw new Exception('Could not split number into digits');
        }

        foreach ($digits as $value) {
            $report = self::TABLE[$report][(int) $value];
        }

        return (10 - $report) % 10;
    }

    private static function getPostAccount(string $postAccount): string
    {
        if (!preg_match('~^(\d+)-(\d+)-(\d)$~', $postAccount, $m)) {
            throw new Exception('Invalid post account number, got `' . $postAccount . '`');
        }

        $participantNumber = self::pad($m[1], 2) . self::pad($m[2], 6) . $m[3];

        if (mb_strlen($participantNumber) !== 9) {
            throw new Exception('The post account number is too long, got `' . $postAccount . '`');
        }

        return $participantNumber;
    }

    /**
     * Get type of document and amount
     */
    private static function getType(?Money $amount): string
    {
        if ($amount === null) {
            $type = '04';
        } elseif ($amount->isNegative()) {
            throw new Exception('Invalid amount. Must be positive, but got: `' . $amount->getAmount() . '`');
        } else {
            $type = '01' . self::pad($amount->getAmount(), 10);
        }

        return $type . self::modulo10($type);
    }
}

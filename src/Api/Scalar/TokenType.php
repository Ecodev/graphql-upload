<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

final class TokenType extends AbstractStringBasedType
{
    /**
     * @var string
     */
    public $description = 'A user token is an lowercase hexadecimal string of 32 characters.';

    /**
     * Validate a token
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValid($value): bool
    {
        return is_string($value) && preg_match('/^[\da-z]{32}$/', $value);
    }
}

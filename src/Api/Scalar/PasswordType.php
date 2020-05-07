<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

class PasswordType extends AbstractStringBasedType
{
    /**
     * @var string
     */
    public $description = 'A password is a string of at least 12 characters';

    /**
     * Validate a token
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValid($value): bool
    {
        return is_string($value) && mb_strlen($value) >= 12;
    }
}

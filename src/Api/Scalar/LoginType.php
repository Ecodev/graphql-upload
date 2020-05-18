<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

class LoginType extends AbstractStringBasedType
{
    /**
     * @var string
     */
    public $description = 'A user login is a non-empty string containing only letters, digits, `.` and `-`.';

    /**
     * Validate a login
     *
     * @param mixed $value
     */
    protected function isValid($value): bool
    {
        return is_string($value) && preg_match('/^[a-zA-Z0-9\\.-]+$/', $value);
    }
}

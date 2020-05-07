<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

class UrlType extends AbstractStringBasedType
{
    /**
     * Validate an URL
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValid($value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL);
    }
}

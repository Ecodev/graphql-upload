<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

use Laminas\Validator\EmailAddress;

/**
 * Represent an email address that can be empty string
 *
 * This support the rare cases where an email is **not** unique in DB. And thus we want the
 * field to be non-null and allow empty string to represent absence of value as we usually do
 * for string fields.
 */
final class NonUniqueEmailType extends AbstractStringBasedType
{
    /**
     * Validate a email
     *
     * @param mixed $value
     */
    protected function isValid($value): bool
    {
        $validator = new EmailAddress();

        return $value === '' || (is_string($value) && $validator->isValid($value));
    }
}

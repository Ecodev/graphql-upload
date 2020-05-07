<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

use GraphQL\Language\AST\StringValueNode;

/**
 * Represent an email address
 *
 * This exceptionally accept empty string as null because email address are often unique
 * in DB and thus can never be empty string to indicate absence of email. So we simplify
 * the client work by accepting empty string and transparently transforming into a null value.
 */
class EmailType extends AbstractStringBasedType
{
    /**
     * Validate a email
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValid($value): bool
    {
        return $value === null || (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL));
    }

    public function serialize($value)
    {
        if ($value === '') {
            return null;
        }

        return parent::serialize($value);
    }

    public function parseValue($value)
    {
        if ($value === '') {
            return null;
        }

        return parent::parseValue($value);
    }

    public function parseLiteral($ast, array $variables = null)
    {
        if ($ast instanceof StringValueNode && $ast->value === '') {
            return null;
        }

        return parent::parseLiteral($ast, $variables);
    }
}

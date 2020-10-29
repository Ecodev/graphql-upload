<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use UnexpectedValueException;

abstract class AbstractStringBasedType extends ScalarType
{
    /**
     * Validate value
     *
     * @param mixed $value
     */
    abstract protected function isValid($value): bool;

    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        // Assuming internal representation of url is always correct:
        return $value;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseValue($value)
    {
        if (!$this->isValid($value)) {
            throw new UnexpectedValueException('Query error: Not a valid ' . $this->name . ': ' . Utils::printSafe($value));
        }

        return $value;
    }

    /**
     * Parses an externally provided literal value to use as an input (e.g. in Query AST)
     *
     * @return null|string
     */
    public function parseLiteral(Node $ast, ?array $variables = null)
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!($ast instanceof StringValueNode)) {
            throw new Error('Query error: Can only parse strings got: ' . $ast->kind, $ast);
        }

        if (!$this->isValid($ast->value)) {
            throw new Error('Query error: Not a valid ' . $this->name, $ast);
        }

        return $ast->value;
    }
}

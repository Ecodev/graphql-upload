<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

use GraphQL\Error\Error;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Money\Money;

abstract class AbstractMoneyType extends ScalarType
{
    /**
     * @param int|string $value
     *
     * @return Money
     */
    abstract protected function createMoney($value): Money;

    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        if ($value instanceof Money) {
            return bcdiv($value->getAmount(), '100', 2);
        }

        return $value;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     *
     * @return Money
     */
    public function parseValue($value)
    {
        if (!is_scalar($value)) {
            throw new \UnexpectedValueException('Cannot represent value as Money: ' . Utils::printSafe($value));
        }

        $value = (string) $value;

        if (!$this->isValid($value)) {
            throw new Error('Query error: Not a valid Money: ' . Utils::printSafe($value));
        }

        $money = $this->createMoney(bcmul($value, '100', 2));

        return $money;
    }

    /**
     * Parses an externally provided literal value to use as an input (e.g. in Query AST)
     *
     * @param Node $ast
     * @param null|array $variables
     *
     * @return Money
     */
    public function parseLiteral($ast, ?array $variables = null)
    {
        if ($ast instanceof StringValueNode || $ast instanceof IntValueNode || $ast instanceof FloatValueNode) {
            return $this->parseValue($ast->value);
        }

        throw new Error('Query error: Can only parse strings got: ' . $ast->kind, $ast);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isValid($value): bool
    {
        return is_string($value) && preg_match('~^-?\d+(\.\d{0,2})?$~', $value);
    }
}

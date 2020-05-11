<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Scalar;

final class ColorType extends AbstractStringBasedType
{
    /**
     * @var string
     */
    public $description = 'A color expressed in hexadecimal CSS notation (eg: `#AA00FF`) or an empty string `""`.';

    /**
     * Validate a color in hexadecimal CSS notation
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValid($value): bool
    {
        return is_string($value) && preg_match('~^(#[[:xdigit:]]{6}|)$~', $value);
    }
}

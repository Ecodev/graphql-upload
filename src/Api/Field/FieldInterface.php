<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Field;

/**
 * Represent a single field configuration
 */
interface FieldInterface
{
    /**
     * Return the single field configuration, including its name
     */
    public static function build(): array;
}

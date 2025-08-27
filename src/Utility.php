<?php

declare(strict_types=1);

namespace GraphQL\Upload;

/**
 * @internal
 */
final class Utility
{
    public static function getPostMaxSize(): int
    {
        return ini_parse_quantity(ini_get('post_max_size') ?: '0');
    }

    /**
     * @param int|numeric-string $value
     */
    public static function toMebibyte(string|int $value): string
    {
        return number_format($value / 1024 / 1024, 2, thousands_separator: "'") . ' MiB';
    }
}

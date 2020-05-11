<?php

declare(strict_types=1);

namespace Ecodev\Felix;

final class Debug
{
    /**
     * Export variables omitting array keys that are strictly numeric
     *
     * By default will output result
     *
     * @param mixed $data
     * @param bool $return
     * @param int $level
     *
     * @return string string representation of variable
     */
    public static function export($data, $return = false, int $level = 0): string
    {
        $result = '';
        if (is_array($data)) {
            $needKey = array_keys($data) !== range(0, count($data) - 1);
            $result .= '[' . PHP_EOL;
            foreach ($data as $key => $value) {
                $result .= str_repeat(' ', 4 * ($level + 1));
                if ($needKey) {
                    $result .= self::export($key, true, $level + 1);
                    $result .= ' => ';
                }

                $result .= self::export($value, true, $level + 1);
                $result .= ',' . PHP_EOL;
            }
            $result .= str_repeat(' ', 4 * $level) . ']';
        } else {
            $result .= var_export($data, true);
        }

        if (!$return) {
            echo $result;
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Ecodev\Felix;

use Ecodev\Felix\Model\Model;
use GraphQL\Doctrine\Definition\EntityID;
use ReflectionClass;

abstract class Utility
{
    /**
     * Returns the short class name of any object, eg: Application\Model\Calendar => Calendar
     *
     * @param object|string $object
     */
    public static function getShortClassName($object): string
    {
        $reflect = new ReflectionClass($object);

        return $reflect->getShortName();
    }

    /**
     * Print a list of files if non empty
     */
    public static function printFiles(string $title, array $files): void
    {
        if (!$files) {
            return;
        }

        echo $title . PHP_EOL . PHP_EOL;

        foreach ($files as $file) {
            echo '    ' . escapeshellarg($file) . PHP_EOL;
        }
        echo PHP_EOL;
    }

    /**
     * Replace EntityID model and don't touch other values
     *
     * @param array $data mix of objects and scalar values
     */
    public static function entityIdToModel(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        foreach ($data as &$value) {
            if ($value instanceof EntityID) {
                $value = $value->getEntity();
            }
        }

        return $data;
    }

    /**
     * Replace object by their ID in the array and don't touch other values
     *
     * Support both AbstractModel and EntityID.
     *
     * @param array $data mix of objects and scalar values
     */
    public static function modelToId(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        foreach ($data as &$value) {
            if ($value instanceof Model || $value instanceof EntityID) {
                $value = $value->getId();
            }
        }

        return $data;
    }

    public static function unique(array $array): array
    {
        $result = [];
        foreach ($array as $value) {
            if (!in_array($value, $result, true)) {
                $result[] = $value;
            }
        }

        return $result;
    }
}

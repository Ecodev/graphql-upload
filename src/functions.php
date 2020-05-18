<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Ecodev\Felix\Debug;
use GraphQL\Doctrine\Types;
use Laminas\Log\LoggerInterface;

/**
 * Returns the type registry
 */
function _types(): Types
{
    global $container;

    return $container->get(Types::class);
}

/**
 * Returns the EM
 */
function _em(): EntityManager
{
    global $container;

    return $container->get(EntityManager::class);
}

/**
 * Returns logger
 */
function _log(): LoggerInterface
{
    global $container;

    return $container->get(LoggerInterface::class);
}

/**
 * Export variables omitting array keys that are strictly numeric
 *
 * By default will output result
 *
 * @param mixed $data
 *
 * @return string string representation of variable
 */
function ve($data, bool $return = false): string
{
    return Debug::export($data, $return);
}

/**
 * Dump all arguments
 */
function v(): void
{
    var_dump(func_get_args());
}

/**
 * Dump all arguments and die
 */
function w(): void
{
    $isHtml = (PHP_SAPI !== 'cli');
    echo "\n_________________________________________________________________________________________________________________________" . ($isHtml ? '</br>' : '') . "\n";
    var_dump(func_get_args());
    echo "\n" . ($isHtml ? '</br>' : '') . '_________________________________________________________________________________________________________________________' . ($isHtml ? '<pre>' : '') . "\n";
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    echo '' . ($isHtml ? '</pre>' : '') . '_________________________________________________________________________________________________________________________' . ($isHtml ? '</br>' : '') . "\n";
    die("script aborted on purpose.\n");
}

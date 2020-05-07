<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api;

use GraphQL\Error\ClientAware;

/**
 * Exception that will show its message to end-user even on production server
 */
class Exception extends \Exception implements ClientAware
{
    public function getCategory(): string
    {
        return 'Permissions';
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}

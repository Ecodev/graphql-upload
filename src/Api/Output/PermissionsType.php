<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Output;

use GraphQL\Type\Definition\ObjectType;

class PermissionsType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Permissions',
            'description' => 'Describe permissions for current user',
            'fields' => [
                'create' => [
                    'type' => self::nonNull(self::boolean()),
                    'description' => 'Whether the current logged in user can create',
                ],
                'read' => [
                    'type' => self::nonNull(self::boolean()),
                    'description' => 'Whether the current logged in user can read',
                ],
                'update' => [
                    'type' => self::nonNull(self::boolean()),
                    'description' => 'Whether the current logged in user can update',
                ],
                'delete' => [
                    'type' => self::nonNull(self::boolean()),
                    'description' => 'Whether the current logged in user can delete',
                ],
            ],
        ];

        parent::__construct($config);
    }
}

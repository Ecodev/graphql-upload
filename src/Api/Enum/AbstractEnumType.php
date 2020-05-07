<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Enum;

use GraphQL\Type\Definition\EnumType;

class AbstractEnumType extends EnumType
{
    public function __construct(array $constants)
    {
        $values = [];
        foreach ($constants as $key => $description) {
            $values[$key] = [
                'value' => $key,
                'description' => $description,
            ];
        }

        $config = [
            'values' => $values,
        ];

        parent::__construct($config);
    }
}

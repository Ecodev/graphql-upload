<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

/**
 * Interface that should be implemented by all AbstractModel
 */
interface Model
{
    /**
     * Get id
     */
    public function getId(): ?int;
}

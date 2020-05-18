<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface HasOwner
{
    /**
     * Get owner
     */
    public function getOwner(): ?User;
}

<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface HasOwner extends Model
{
    /**
     * Get owner
     *
     * @return null|User
     */
    public function getOwner(): ?User;
}

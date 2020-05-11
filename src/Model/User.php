<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface User extends Model
{
    /**
     * Get login (eg: johndoe)
     */
    public function getLogin(): ?string;

    /**
     * Get full name
     */
    public function getName(): string;

    /**
     * Returns the user role
     */
    public function getRole(): string;
}

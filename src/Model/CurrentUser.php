<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

/**
 * Tiny bit dirty way to easily pass current user from app to Felix
 */
final class CurrentUser
{
    /**
     * @var null|User
     */
    private static $currentUser = null;

    /**
     * Set currently logged in user
     */
    public static function set(?User $user): void
    {
        self::$currentUser = $user;
    }

    /**
     * Returns currently logged user or null
     */
    public static function get(): ?User
    {
        return self::$currentUser;
    }
}

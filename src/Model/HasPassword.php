<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

/**
 * Trait for a user with a password and password reset capabilities
 */
interface HasPassword
{
    /**
     * Hash and change the user password
     */
    public function setPassword(string $password): void;

    /**
     * Returns the hashed password
     */
    public function getPassword(): string;

    /**
     * Generate a new random token to reset password
     */
    public function createToken(): string;

    /**
     * Destroy existing token
     */
    public function revokeToken(): void;

    /**
     * Check if token is valid.
     */
    public function isTokenValid(): bool;
}

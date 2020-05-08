<?php

declare(strict_types=1);

namespace Ecodev\Felix\Repository;

use Ecodev\Felix\Model\User;

/**
 * Interface for repositories whose objects access must be limited for user
 */
interface LimitedAccessSubQuery
{
    /**
     * Returns pure SQL to get ID of all objects that are accessible to given user.
     *
     * If no filter should be applied, you should return empty string ''
     *
     * @param null|User $user
     *
     * @return string
     */
    public function getAccessibleSubQuery(?\Ecodev\Felix\Model\User $user): string;
}

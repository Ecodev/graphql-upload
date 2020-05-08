<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Blog\Repository;

use Doctrine\ORM\EntityRepository;
use Ecodev\Felix\Repository\LimitedAccessSubQuery;

/**
 * A fake repository
 */
final class PostRepository extends EntityRepository implements LimitedAccessSubQuery
{
    public function getAccessibleSubQuery(?\Ecodev\Felix\Model\User $user): string
    {
        if (!$user) {
            return 'SELECT id FROM post WHERE is_public = 1';
        }

        return '';
    }
}

<?php

declare(strict_types=1);

namespace Ecodev\Felix\Repository\Traits;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ecodev\Felix\Model\User;
use Ecodev\Felix\ORM\Query\Filter\AclFilter;

/**
 * Trait for common method of repository
 */
trait Repository
{
    /**
     * @return EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * @return ClassMetadata
     */
    abstract protected function getClassMetadata();

    /**
     * Returns the AclFilter to fetch ACL filtering SQL
     */
    public function getAclFilter(): AclFilter
    {
        /** @var AclFilter $aclFilter */
        $aclFilter = $this->getEntityManager()->getFilters()->getFilter(AclFilter::class);

        return $aclFilter;
    }

    /**
     * Return native SQL query to get all ID
     */
    protected function getAllIdsQuery(): string
    {
        $connection = $this->getEntityManager()->getConnection();
        $qb = $connection->createQueryBuilder()
            ->select('id')
            ->from($connection->quoteIdentifier($this->getClassMetadata()->getTableName()));

        return $qb->getSQL();
    }

    /**
     * Return native SQL query to get all ID of object owned by given user
     */
    protected function getAllIdsForOwnerQuery(User $user): string
    {
        $connection = $this->getEntityManager()->getConnection();
        $qb = $connection->createQueryBuilder()
            ->select('id')
            ->from($connection->quoteIdentifier($this->getClassMetadata()->getTableName()))
            ->andWhere('owner_id = ' . $user->getId());

        return $qb->getSQL();
    }
}

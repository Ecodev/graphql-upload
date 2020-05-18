<?php

declare(strict_types=1);

namespace Ecodev\Felix\ORM\Query\Filter;

use Doctrine\Common\Cache\ClearableCache;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Ecodev\Felix\Model\User;
use Ecodev\Felix\Repository\LimitedAccessSubQuery;

/**
 * Automatically filter objects according to what user is allowed to access.
 *
 * Most objects will be filtered with subqueries because they implement `LimitedAccessSubQuery`
 */
final class AclFilter extends SQLFilter
{
    /**
     * @var null|User
     */
    private $user;

    /**
     * Cache of subqueries to get accessible IDs.
     * Keys are class name of object
     *
     * @var array possible values of the array are: null|string
     */
    private $subQueriesCache = [];

    /**
     * The number of time the filter has been deactivated
     *
     * @var int
     */
    private $disabledCount = 0;

    /**
     * Disable the ACL filter forever
     *
     * The only way to re-enable it is to create a new instance.
     */
    public function disableForever(): void
    {
        $this->setEnabled(false);
    }

    /**
     * Run the given callable with ACL temporarily disabled
     *
     * This method MUST be used instead of `$entityManager->getFilters()->enable()` because
     * that method destroy the filter object and thus losing the current user. So to keep
     * our internal state intact we must implement a custom enable/disable mechanism.
     *
     * @return mixed whatever the callable returned
     */
    public function runWithoutAcl(callable $callable)
    {
        $this->setEnabled(false);

        try {
            return $callable();
        } finally {
            $this->setEnabled(true);
        }
    }

    /**
     * Enable or disable the filter
     */
    private function setEnabled(bool $enabled): void
    {
        if ($enabled) {
            --$this->disabledCount;
        } else {
            ++$this->disabledCount;
        }
    }

    /**
     * @param string $targetTableAlias
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($this->disabledCount > 0) {
            return '';
        }

        $repository = _em()->getRepository($targetEntity->name);
        $result = '';
        if ($repository instanceof LimitedAccessSubQuery) {
            $subQuery = $this->getSubQuery($targetEntity->name, $repository);
            $result = $this->buildSql($targetTableAlias, 'id', $subQuery);
        }

        return $result;
    }

    /**
     * Set the current user for which we should apply access limitations
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
        $this->resetCache();

        $cache = _em()->getConfiguration()->getQueryCacheImpl();
        if ($cache instanceof ClearableCache) {
            $cache->deleteAll();
        }
    }

    private function resetCache(): void
    {
        $this->subQueriesCache = [];
    }

    private function buildSql(string $targetTableAlias, string $field, string $subQuery): string
    {
        if (!$subQuery) {
            return '';
        }

        return $targetTableAlias . '.' . $field . ' IN (' . $subQuery . ')';
    }

    private function getSubQuery(string $class, LimitedAccessSubQuery $repository): string
    {
        if (!array_key_exists($class, $this->subQueriesCache)) {
            $subQuery = $repository->getAccessibleSubQuery($this->user);
            $this->subQueriesCache[$class] = $subQuery;
        }

        return $this->subQueriesCache[$class];
    }
}

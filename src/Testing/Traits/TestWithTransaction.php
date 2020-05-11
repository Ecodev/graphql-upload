<?php

declare(strict_types=1);

namespace Ecodev\Felix\Testing\Traits;

use Doctrine\ORM\EntityManager;

/**
 * Allow to run test within a database transaction, so database will be unchanged after test
 */
trait TestWithTransaction
{
    /**
     * Get EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return _em();
    }

    /**
     * Start transaction
     */
    public function setUp(): void
    {
        $this->getEntityManager()->clear();
        $this->getEntityManager()->beginTransaction();
    }

    /**
     * Cancel transaction, to undo all changes made
     */
    public function tearDown(): void
    {
        $this->getEntityManager()->rollback();
        $this->getEntityManager()->clear();
        $this->getEntityManager()->getConnection()->close();
    }
}

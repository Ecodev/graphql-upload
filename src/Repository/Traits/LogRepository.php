<?php

declare(strict_types=1);

namespace Ecodev\Felix\Repository\Traits;

use Cake\Chronos\Chronos;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Ecodev\Felix\Model\User;
use Ecodev\Felix\Repository\LogRepository as LogRepositoryInterface;
use Laminas\Log\Logger;
use PDO;

trait LogRepository
{
    /**
     * @return EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias
     * @param string $indexBy the index for the from
     *
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    /**
     * This should NOT be called directly, instead use `_log()` to log stuff
     */
    public function log(array $event): void
    {
        $event['creation_date'] = Chronos::instance($event['creation_date'])->toIso8601String();
        $event['extra'] = json_encode($event['extra']);

        $this->getEntityManager()->getConnection()->insert('log', $event);
    }

    /**
     * Returns whether the current IP often failed to login
     */
    public function loginFailedOften(): bool
    {
        return $this->failedOften(LogRepositoryInterface::LOGIN, LogRepositoryInterface::LOGIN_FAILED);
    }

    public function updatePasswordFailedOften(): bool
    {
        return $this->failedOften(LogRepositoryInterface::UPDATE_PASSWORD, LogRepositoryInterface::UPDATE_PASSWORD_FAILED);
    }

    private function failedOften(string $success, string $failed): bool
    {
        if (PHP_SAPI === 'cli') {
            $ip = 'script';
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        $select = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('message')
            ->from('log')
            ->andWhere('priority = :priority')
            ->setParameter('priority', Logger::INFO)
            ->andWhere('message IN (:message)')
            ->setParameter('message', [$success, $failed], Connection::PARAM_STR_ARRAY)
            ->andWhere('creation_date > DATE_SUB(NOW(), INTERVAL 30 MINUTE)')
            ->andWhere('ip = :ip')
            ->setParameter('ip', $ip)
            ->orderBy('id', 'DESC');

        $events = $select->execute()->fetchAll(PDO::FETCH_COLUMN);

        // Goes from present to past and count failure, until the last time we succeeded logging in
        $failureCount = 0;
        foreach ($events as $event) {
            if ($event === $success) {
                break;
            }
            ++$failureCount;
        }

        return $failureCount > 20;
    }

    /**
     * Delete log entries which are errors/warnings and older than one month
     * We always keep Logger::INFO level because we use it for statistics
     *
     * @return int the count deleted logs
     */
    public function deleteOldLogs(): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $query = $connection->createQueryBuilder()
            ->delete('log')
            ->andWhere('log.priority != :priority OR message = :message')
            ->setParameter('priority', Logger::INFO)
            ->setParameter('message', LogRepositoryInterface::LOGIN_FAILED)
            ->andWhere('log.creation_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)');

        $connection->query('LOCK TABLES `log` WRITE;');
        $count = $query->execute();
        $connection->query('UNLOCK TABLES;');

        return $count;
    }

    public function getLoginDate(User $user, bool $first): ?Chronos
    {
        $qb = $this->createQueryBuilder('log')
            ->select('log.creationDate')
            ->andWhere('log.creator = :user')
            ->andWhere('log.message = :message')
            ->setParameter('user', $user)
            ->setParameter('message', LogRepositoryInterface::LOGIN)
            ->addOrderBy('log.creationDate', $first ? 'ASC' : 'DESC');

        $result = $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();

        return $result['creationDate'] ?? null;
    }
}

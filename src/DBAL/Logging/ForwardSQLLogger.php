<?php

declare(strict_types=1);

namespace Ecodev\Felix\DBAL\Logging;

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;

/**
 * A SQL logger that forward logs to Laminas Log
 */
final class ForwardSQLLogger extends DebugStack implements SQLLogger
{
    public function stopQuery(): void
    {
        if ($this->enabled) {
            parent::stopQuery();

            $this->forwardLog($this->queries[$this->currentQuery]);
        }
    }

    /**
     * Forward query to file logger
     *
     * @param array $query
     */
    private function forwardLog(array $query): void
    {
        $extra = [
            'params' => $query['params'],
            'time' => number_format($query['executionMS'], 6),
        ];

        // Here we cannot inject the logger via DI, or it would be created too early and
        // break unit tests by creating two parallel connection to DB and thus timeout
        // when a tests's transaction is pending but a log is trying to be written on the other connection
        _log()->debug($query['sql'], $extra);
    }
}

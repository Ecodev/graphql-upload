<?php

declare(strict_types=1);

namespace Ecodev\Felix\Repository;

use Cake\Chronos\Chronos;
use Ecodev\Felix\Model\User;
use Laminas\Log\Logger;

interface LogRepository
{
    /**
     * Log message to be used when user log in
     */
    const LOGIN = 'login';

    /**
     * Log message to be used when user cannot log in
     */
    const LOGIN_FAILED = 'login failed';

    /**
     * Log message to be used when user change his password
     */
    const UPDATE_PASSWORD = 'update password';

    /**
     * Log message to be used when user cannot change his password
     */
    const UPDATE_PASSWORD_FAILED = 'update password failed';

    /**
     * Log message to be used when trying to send email but it's already running
     */
    const MAILER_LOCKED = 'Unable to obtain lock for mailer, try again later.';

    /**
     * This should NOT be called directly, instead use `_log()` to log stuff
     *
     * @param array $event
     */
    public function log(array $event): void;

    /**
     * Returns whether the current IP often failed to login
     *
     * @return bool
     */
    public function loginFailedOften(): bool;

    public function updatePasswordFailedOften(): bool;

    /**
     * Delete log entries which are errors/warnings and older than one month
     * We always keep Logger::INFO level because we use it for statistics
     *
     * @return int the count deleted logs
     */
    public function deleteOldLogs(): int;

    public function getLoginDate(User $user, bool $first): ?Chronos;
}

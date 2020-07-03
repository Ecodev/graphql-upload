<?php

declare(strict_types=1);

namespace Ecodev\Felix\Log;

use Ecodev\Felix\Model\CurrentUser;
use Ecodev\Felix\Repository\LogRepository;
use Laminas\Log\Writer\AbstractWriter;

class DbWriter extends AbstractWriter
{
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(LogRepository $logRepository, string $baseUrl, $options = null)
    {
        parent::__construct($options);
        $this->logRepository = $logRepository;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Write a message to the log
     *
     * @param array $event log data event
     */
    final protected function doWrite(array $event): void
    {
        $completedEvent = $this->completeEvent($event);
        $this->logRepository->log($completedEvent);
    }

    protected function completeEvent(array $event): array
    {
        $envData = $this->getEnvData();
        $event = array_merge($event, $envData);

        // If we are logging PHP errors, then we include all known information in message
        if ($event['extra']['errno'] ?? false) {
            $event['message'] .= "\nStacktrace:\n" . $this->getStacktrace();
        }

        // Security hide clear text password
        unset($event['extra']['password']);

        $event['creation_date'] = $event['timestamp'];
        unset($event['timestamp'], $event['priorityName']);

        return $event;
    }

    /**
     * Retrieve dynamic information from environment to be logged.
     */
    private function getEnvData(): array
    {
        $user = CurrentUser::get();

        if (PHP_SAPI === 'cli') {
            global $argv;
            $request = $argv;
            $ip = 'script';
            $url = implode(' ', $argv);
            $referer = '';
        } else {
            $request = $_REQUEST;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $url = $this->baseUrl . $_SERVER['REQUEST_URI'];
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
        }

        $request = $this->removeSensitiveData($request);

        $envData = [
            'creator_id' => $user ? $user->getId() : null,
            'url' => $url,
            'referer' => $referer,
            'request' => json_encode($request, JSON_PRETTY_PRINT),
            'ip' => $ip,
        ];

        return $envData;
    }

    protected function removeSensitiveData(array $request): array
    {
        return $request;
    }

    /**
     * Returns the backtrace excluding the most recent calls to this function so we only get the interesting parts
     */
    private function getStacktrace(): string
    {
        ob_start();
        @debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = ob_get_contents();
        ob_end_clean();

        if ($trace === false) {
            return 'Could not get stacktrace';
        }

        // Remove first items from backtrace as it's this function and previous logging functions which is not interesting
        $shortenTrace = preg_replace('/^#[0-4]\s+[^\n]*\n/m', '', $trace);

        if ($shortenTrace === null) {
            return $trace;
        }

        // Renumber backtrace items.
        $renumberedTrace = preg_replace_callback('/^#(\d+)/m', function ($matches) {
            return '#' . ($matches[1] - 5);
        }, $shortenTrace);

        if ($renumberedTrace === null) {
            return $shortenTrace;
        }

        return $renumberedTrace;
    }
}

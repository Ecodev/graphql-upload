<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api;

use Doctrine\DBAL\Exception\DriverException;
use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * A thin wrapper to serve GraphQL via HTTP or CLI
 */
class Server
{
    /**
     * @var StandardServer
     */
    private $server;

    /**
     * @var ServerConfig
     */
    private $config;

    public function __construct(Schema $schema, bool $debug, array $rootValue = [])
    {
        GraphQL::setDefaultFieldResolver(new FilteredFieldResolver());

        $debugFlag = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;

        $this->config = ServerConfig::create([
            'schema' => $schema,
            'queryBatching' => true,
            'debugFlag' => $debug ? $debugFlag : DebugFlag::NONE,
            'errorsHandler' => function (array $errors, callable $formatter) {
                $result = [];
                foreach ($errors as $e) {
                    $result[] = $this->handleError($e, $formatter);
                }

                return $result;
            },
            'rootValue' => $rootValue,
        ]);

        $this->server = new StandardServer($this->config);
    }

    /**
     * @return ExecutionResult|ExecutionResult[]
     */
    public function execute(ServerRequestInterface $request)
    {
        if (!$request->getParsedBody()) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        }

        // Affect it to global request so it is available for log purpose in case of error
        $_REQUEST = $request->getParsedBody();

        // Set current session as the only context we will ever need
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $this->config->setContext($session);

        return $this->server->executePsrRequest($request);
    }

    /**
     * Send response using standard PHP `header()` and `echo`.
     *
     * Most of the time you should not use this and instead return the
     * response directly to the middleware.
     *
     * @param ExecutionResult|ExecutionResult[] $result
     */
    public function sendHttp($result): void
    {
        $this->server->getHelper()->sendResponse($result);
    }

    /**
     * Send response to CLI
     */
    public function sendCli(ExecutionResult $result): void
    {
        echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
    }

    /**
     * Custom error handler to log in DB and show trigger messages to end-user
     */
    private function handleError(Throwable $exception, callable $formatter): array
    {
        // Always log exception in DB (and by email)
        _log()->err($exception->__toString());

        // If we are absolutely certain that the error comes from one of our trigger with a custom message for end-user,
        // then wrap the exception to make it showable to the end-user
        $previous = $exception->getPrevious();
        if ($previous instanceof DriverException && $previous->getSQLState() === '45000' && $previous->getPrevious() && $previous->getPrevious()->getPrevious()) {
            $message = $previous->getPrevious()->getPrevious()->getMessage();
            $userMessage = (string) preg_replace('~SQLSTATE\[45000\]: <<Unknown error>>: \d+ ~', '', $message, -1, $count);
            if ($count === 1) {
                $exception = new Exception($userMessage, 0, $exception);
            }
        }

        return $formatter($exception);
    }
}

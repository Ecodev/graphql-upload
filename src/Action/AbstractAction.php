<?php

declare(strict_types=1);

namespace Ecodev\Felix\Action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

abstract class AbstractAction implements MiddlewareInterface
{
    protected function createError(string $message): ResponseInterface
    {
        $response = new JsonResponse(['error' => $message]);

        return $response->withStatus(404, $message);
    }
}

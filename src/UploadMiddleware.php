<?php

declare(strict_types=1);

namespace GraphQL\Upload;

use GraphQL\Error\InvariantViolation;
use GraphQL\Server\RequestError;
use GraphQL\Utils\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UploadMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->processRequest($request);

        return $handler->handle($request);
    }

    /**
     * Process the request and return either a modified request or the original one.
     */
    public function processRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $contentType = $request->getHeader('content-type')[0] ?? '';

        if (mb_stripos($contentType, 'multipart/form-data') !== false) {
            $this->validateParsedBody($request);
            $request = $this->parseUploadedFiles($request);
        }

        return $request;
    }

    /**
     * Inject uploaded files defined in the 'map' key into the 'variables' key.
     */
    private function parseUploadedFiles(ServerRequestInterface $request): ServerRequestInterface
    {
        /** @var string[] $bodyParams */
        $bodyParams = $request->getParsedBody();

        $map = $this->decodeArray($bodyParams, 'map');
        $result = $this->decodeArray($bodyParams, 'operations');

        foreach ($map as $fileKey => $locations) {
            foreach ($locations as $location) {
                $items = &$result;
                foreach (explode('.', $location) as $key) {
                    if (!isset($items[$key]) || !is_array($items[$key])) {
                        $items[$key] = [];
                    }
                    $items = &$items[$key];
                }

                $items = $request->getUploadedFiles()[$fileKey];
            }
        }

        return $request
            ->withHeader('content-type', 'application/json')
            ->withParsedBody($result);
    }

    /**
     * Validates that the request meet our expectations.
     */
    private function validateParsedBody(ServerRequestInterface $request): void
    {
        $bodyParams = $request->getParsedBody();

        if (null === $bodyParams) {
            throw new InvariantViolation(
                'PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got null'
            );
        }

        if (!is_array($bodyParams)) {
            throw new RequestError(
                'GraphQL Server expects JSON object or array, but got ' . Utils::printSafeJson($bodyParams)
            );
        }

        if (empty($bodyParams)) {
            throw new InvariantViolation(
                'PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got empty array'
            );
        }
    }

    /**
     * @param string[] $bodyParams
     *
     * @return string[][]
     */
    private function decodeArray(array $bodyParams, string $key): array
    {
        if (!isset($bodyParams[$key])) {
            throw new RequestError("The request must define a `$key`");
        }

        $value = json_decode($bodyParams[$key], true);
        if (!is_array($value)) {
            throw new RequestError("The `$key` key must be a JSON encoded array");
        }

        return $value;
    }
}

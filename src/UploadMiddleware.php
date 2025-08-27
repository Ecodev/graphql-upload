<?php

declare(strict_types=1);

namespace GraphQL\Upload;

use GraphQL\Error\InvariantViolation;
use GraphQL\Server\RequestError;
use GraphQL\Utils\Utils;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UploadMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeader('content-type')[0] ?? '';

        if (mb_stripos($contentType, 'multipart/form-data') !== false) {
            $error = $this->postMaxSizeError($request);
            if ($error) {
                return $error;
            }

            $this->validateParsedBody($request);
            $request = $this->parseUploadedFiles($request);
        }

        return $handler->handle($request);
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

        $uploadedFiles = $request->getUploadedFiles();
        foreach ($map as $fileKey => $locations) {
            foreach ($locations as $location) {
                $items = &$result;
                foreach (explode('.', $location) as $key) {
                    if (!isset($items[$key]) || !is_array($items[$key])) {
                        $items[$key] = [];
                    }
                    $items = &$items[$key];
                }

                if (!array_key_exists($fileKey, $uploadedFiles)) {
                    throw new RequestError(
                        "GraphQL query declared an upload in `$location`, but no corresponding file were actually uploaded",
                    );
                }

                $items = $uploadedFiles[$fileKey];
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
                'PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got null',
            );
        }

        if (!is_array($bodyParams)) {
            throw new RequestError(
                'GraphQL Server expects JSON object or array, but got ' . Utils::printSafeJson($bodyParams),
            );
        }

        if (empty($bodyParams)) {
            throw new InvariantViolation(
                'PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got empty array',
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

    private function postMaxSizeError(ServerRequestInterface $request): ?ResponseInterface
    {
        $contentLength = $request->getServerParams()['CONTENT_LENGTH'] ?? 0;
        $postMaxSize = Utility::getPostMaxSize();
        if ($contentLength && $contentLength > $postMaxSize) {
            $contentLength = Utility::toMebibyte($contentLength);
            $postMaxSize = Utility::toMebibyte($postMaxSize);

            return new JsonResponse(
                ['message' => "The server `post_max_size` is configured to accept $postMaxSize, but received $contentLength"],
                413,
            );
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace GraphQL\Upload;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

final class UploadType extends ScalarType
{
    public string $name = 'Upload';

    public ?string $description
        = 'The `Upload` special type represents a file to be uploaded in the same HTTP request as specified by
 [graphql-multipart-request-spec](https://github.com/jaydenseric/graphql-multipart-request-spec).';

    /**
     * Serializes an internal value to include in a response.
     */
    public function serialize(mixed $value): never
    {
        throw new InvariantViolation('`Upload` cannot be serialized');
    }

    /**
     * Parses an externally provided value (query variable) to use as an input.
     */
    public function parseValue(mixed $value): UploadedFileInterface
    {
        if (!$value instanceof UploadedFileInterface) {
            throw new UnexpectedValueException('Could not get uploaded file, be sure to conform to GraphQL multipart request specification. Instead got: ' . Utils::printSafe($value));
        }

        return $value;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        throw new Error('`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification. Instead got: ' . $valueNode->kind, $valueNode);
    }
}

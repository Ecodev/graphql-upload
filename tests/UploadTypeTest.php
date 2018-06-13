<?php

declare(strict_types=1);

namespace GraphQLTests\Upload;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Upload\UploadType;
use GraphQLTests\Upload\Psr7\PsrUploadedFileStub;
use PHPUnit\Framework\TestCase;

class UploadTypeTest extends TestCase
{
    public function testCanParseUploadedFileInstance(): void
    {
        $type = new UploadType();
        $file = new PsrUploadedFileStub('image.jpg', 'image/jpeg');
        $actual = $type->parseValue($file);
        self::assertSame($file, $actual);
    }

    public function testCannotParseNonUploadedFileInstance(): void
    {
        $type = new UploadType();
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not get uploaded file, be sure to conform to GraphQL multipart request specification. Instead got: foo');

        $type->parseValue('foo');
    }

    public function testCanNeverBeSerialized(): void
    {
        $type = new UploadType();
        $this->expectException(InvariantViolation::class);
        $this->expectExceptionMessage('`Upload` cannot be serialized');

        $type->serialize('foo');
    }

    public function testCanNeverParseLiteral(): void
    {
        $type = new UploadType();
        $node = new StringValueNode(['value' => 'foo']);

        $this->expectException(Error::class);
        $this->expectExceptionMessage('`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification. Instead got: StringValue');
        $type->parseLiteral($node);
    }
}

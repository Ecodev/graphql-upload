<?php

declare(strict_types=1);

namespace GraphQLTests\Upload;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Upload\UploadError;
use GraphQL\Upload\UploadType;
use GraphQL\Upload\Utility;
use GraphQLTests\Upload\Psr7\PsrUploadedFileStub;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use UnexpectedValueException;

final class UploadTypeTest extends TestCase
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
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not get uploaded file, be sure to conform to GraphQL multipart request specification. Instead got: "foo"');

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

    /**
     * @param class-string<Throwable> $e
     */
    #[DataProvider('providerUploadErrorWillThrow')]
    public function testUploadErrorWillThrow(int $errorStatus, string $expectedMessage, string $e = UploadError::class): void
    {
        $type = new UploadType();
        $file = new PsrUploadedFileStub('image.jpg', 'image/jpeg', $errorStatus);

        $this->expectException($e);
        $this->expectExceptionMessage($expectedMessage);

        $type->parseValue($file);
    }

    /**
     * @return iterable<array{0: int, 1: string, 2?: class-string<Throwable>}>
     */
    public static function providerUploadErrorWillThrow(): iterable
    {
        yield [UPLOAD_ERR_CANT_WRITE, 'Failed to write file to disk'];
        yield [UPLOAD_ERR_EXTENSION, 'A PHP extension stopped the upload'];
        yield [UPLOAD_ERR_FORM_SIZE, 'The file exceeds the `MAX_FILE_SIZE` directive that was specified in the HTML form'];
        yield [UPLOAD_ERR_INI_SIZE, 'The file exceeds the `upload_max_filesize` of ' . Utility::toMebibyte(Utility::getUploadMaxFilesize())];
        yield [UPLOAD_ERR_NO_FILE, 'No file was uploaded'];
        yield [UPLOAD_ERR_NO_TMP_DIR, 'Missing a temporary folder'];
        yield [UPLOAD_ERR_PARTIAL, 'The file was only partially uploaded'];
        yield [5, 'Unsupported UPLOAD_ERR_* constant value: 5', Exception::class];
    }
}

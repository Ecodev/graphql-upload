<?php

declare(strict_types=1);

namespace GraphQLTests\Upload\Psr7;

use Laminas\Diactoros\UploadedFile;

final class PsrUploadedFileStub extends UploadedFile
{
    public function __construct(string $clientFilename, string $clientMediaType)
    {
        parent::__construct('foo', 123, UPLOAD_ERR_OK, $clientFilename, $clientMediaType);
    }
}

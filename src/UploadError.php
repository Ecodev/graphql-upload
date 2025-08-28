<?php

declare(strict_types=1);

namespace GraphQL\Upload;

use Exception;
use GraphQL\Error\Error;

final class UploadError extends Error
{
    public function __construct(int $uploadError)
    {
        parent::__construct('File upload: ' . $this->getMessageFromUploadError($uploadError));
    }

    private function getMessageFromUploadError(int $uploadError): string
    {
        return match ($uploadError) {
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload',
            UPLOAD_ERR_FORM_SIZE => 'The file exceeds the `MAX_FILE_SIZE` directive that was specified in the HTML form',
            UPLOAD_ERR_INI_SIZE => 'The file exceeds the `upload_max_filesize` of ' . Utility::toMebibyte(Utility::getUploadMaxFilesize()),
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded',
            default => throw new Exception('Unsupported UPLOAD_ERR_* constant value: ' . $uploadError),
        };
    }
}

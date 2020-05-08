<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model\Traits;

/**
 * An uploaded file
 */
trait File
{
    use AbstractFile;

    protected function getBasePath(): string
    {
        return 'data/file/';
    }

    protected function getAcceptedMimeTypes(): array
    {
        return [
            'image/bmp',
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/svg+xml',
            'image/webp',
            'application/pdf',
            'application/x-pdf',
        ];
    }
}

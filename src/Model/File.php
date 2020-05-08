<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface File
{
    /**
     * Get absolute path to file on disk
     */
    public function getPath(): string;

    /**
     * Get filename (without path)
     */
    public function getFilename(): string;

    /**
     * Set filename (without path)
     */
    public function setFilename(string $filename): void;
}

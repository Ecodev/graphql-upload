<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface File
{
    /**
     * Get absolute path to file on disk
     *
     * @API\Exclude
     *
     * @return string
     */
    public function getPath(): string;
}

<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface Image extends File
{
    /**
     * Get image height
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * Get mime
     *
     * @return string
     */
    public function getMime(): string;

    /**
     * Get filename (without path)
     *
     * @return string
     */
    public function getFilename(): string;
}

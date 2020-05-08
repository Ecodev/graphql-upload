<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

interface Image extends File
{
    public function getWidth(): int;

    /**
     * Set image width
     */
    public function setWidth(int $width): void;

    /**
     * Get image height
     */
    public function getHeight(): int;

    /**
     * Set image height
     */
    public function setHeight(int $height): void;

    /**
     * Get mime
     */
    public function getMime(): string;
}

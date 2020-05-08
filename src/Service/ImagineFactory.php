<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;

class ImagineFactory
{
    /**
     * Return the preferred driver available on this system
     *
     * @param ContainerInterface $container
     *
     * @return ImagineInterface
     */
    public function __invoke(ContainerInterface $container): ImagineInterface
    {
        if (class_exists('Gmagick')) {
            return new \Imagine\Gmagick\Imagine();
        }

        if (class_exists('Imagick')) {
            return new \Imagine\Imagick\Imagine();
        }

        throw new \Exception('Gmagick and Imagick are missing, install one of those module');
    }
}

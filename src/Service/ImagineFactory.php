<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Exception;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class ImagineFactory implements FactoryInterface
{
    /**
     * Return the preferred driver available on this system
     *
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ImagineInterface
    {
        if (class_exists('Gmagick')) {
            return new \Imagine\Gmagick\Imagine();
        }

        if (class_exists('Imagick')) {
            return new \Imagine\Imagick\Imagine();
        }

        throw new Exception('Gmagick and Imagick are missing, install one of those module');
    }
}

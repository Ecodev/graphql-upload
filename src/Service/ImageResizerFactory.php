<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class ImageResizerFactory implements FactoryInterface
{
    /**
     * Return the image service to be used to resize images
     *
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ImageResizer
    {
        $imagine = $container->get(ImagineInterface::class);

        return new ImageResizer($imagine);
    }
}

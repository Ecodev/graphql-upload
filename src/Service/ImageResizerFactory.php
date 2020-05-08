<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;

class ImageResizerFactory
{
    /**
     * Return the image service to be used to resize images
     *
     * @param ContainerInterface $container
     *
     * @return ImageResizer
     */
    public function __invoke(ContainerInterface $container): ImageResizer
    {
        $imagine = $container->get(ImagineInterface::class);

        return new ImageResizer($imagine);
    }
}

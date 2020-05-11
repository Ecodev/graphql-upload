<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\TemplatePathStack;

class RendererFactory implements FactoryInterface
{
    /**
     * Return a configured mailer
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return RendererInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RendererInterface
    {
        $renderer = new PhpRenderer();

        $helperPluginManager = $container->get(HelperPluginManager::class);
        $renderer->setHelperPluginManager($helperPluginManager);

        $resolver = new TemplatePathStack();
        $resolver->addPath('server/templates/emails');
        $renderer->setResolver($resolver);

        return $renderer;
    }
}

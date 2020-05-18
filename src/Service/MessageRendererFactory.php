<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\RendererInterface;

/**
 * Service to render message to HTML
 */
final class MessageRendererFactory implements FactoryInterface
{
    /**
     * Return a configured mailer
     *
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MessageRenderer
    {
        $viewRenderer = $container->get(RendererInterface::class);
        $config = $container->get('config');

        return new MessageRenderer($viewRenderer, $config['hostname']);
    }
}

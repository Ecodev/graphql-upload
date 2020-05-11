<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TransportFactory implements FactoryInterface
{
    /**
     * Return a configured mail transport
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return TransportInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportInterface
    {
        $config = $container->get('config');

        // Setup SMTP transport, or a mock one
        $configSmtp = $config['smtp'] ?? null;
        if ($configSmtp) {
            $transport = new Smtp();
            $options = new SmtpOptions($config['smtp']);
            $transport->setOptions($options);
        } else {
            $transport = new InMemory();
        }

        return $transport;
    }
}

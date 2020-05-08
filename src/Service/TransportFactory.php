<?php

declare(strict_types=1);

namespace Ecodev\Felix\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Transport\TransportInterface;

class TransportFactory
{
    /**
     * Return a configured mail transport
     *
     * @param ContainerInterface $container
     *
     * @return TransportInterface
     */
    public function __invoke(ContainerInterface $container): TransportInterface
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

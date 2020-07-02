<?php

declare(strict_types=1);

namespace Ecodev\Felix\Log;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class LoggerFactory implements FactoryInterface
{
    /**
     * @var null|Logger
     */
    private $logger;

    /**
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Logger
    {
        if (!$this->logger) {
            // Log to file
            $this->logger = new Logger();
            $fileWriter = new Stream('logs/all.log');
            $this->logger->addWriter($fileWriter);

            // Log to DB
            $dbWriter = $container->get(DbWriter::class);
            $dbWriter->addFilter(Logger::INFO);
            $this->logger->addWriter($dbWriter);
        }

        return $this->logger;
    }
}
<?php

declare(strict_types=1);

namespace Ecodev\Felix\Log;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LoggerFactory implements FactoryInterface
{
    /**
     * @var null|Logger
     */
    private $logger;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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

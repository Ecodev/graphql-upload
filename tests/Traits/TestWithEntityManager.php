<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Traits;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Laminas\ServiceManager\ServiceManager;

/**
 * Trait to easily set up a dummy entity manager
 */
trait TestWithEntityManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function setUp(): void
    {
        // Create the entity manager
        $config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/Blog/Model'], true, null, null, false);
        $conn = ['url' => 'sqlite:///:memory:'];
        $this->entityManager = EntityManager::create($conn, $config);

        global $container;
        $container = new ServiceManager([
            'factories' => [
                EntityManager::class => function () {
                    return $this->entityManager;
                },
            ],
        ]);
    }

    public function tearDown(): void
    {
        global $container;
        $container = null;
    }
}

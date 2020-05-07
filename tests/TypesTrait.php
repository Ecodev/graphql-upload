<?php

declare(strict_types=1);

namespace EcodevTests\Felix;

use DateTime;
use GraphQL\Doctrine\Types;
use Laminas\ServiceManager\ServiceManager;

/**
 * Trait to easily set up types and assert them
 */
trait TypesTrait
{
    use EntityManagerTrait;

    /**
     * @var Types
     */
    private $types;

    public function setUp(): void
    {
        $this->setUpEntityManager();

        $customTypes = new ServiceManager([
            'invokables' => [
            ],
            'aliases' => [
                'datetime' => DateTime::class, // Declare alias for Doctrine type to be used for filters
            ],
        ]);

        $this->types = new Types($this->entityManager, $customTypes);
    }
}

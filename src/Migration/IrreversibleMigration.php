<?php

declare(strict_types=1);

namespace Ecodev\Felix\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Abstract migration to always forbid down migrations because we don't want
 * to create more work maintenance than necessary
 */
abstract class IrreversibleMigration extends AbstractMigration
{
    final public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}

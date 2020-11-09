<?php

declare(strict_types=1);

namespace Ecodev\Felix\Acl\Assertion;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

final class One implements AssertionInterface
{
    /**
     * @var AssertionInterface[]
     */
    private $asserts;

    /**
     * Check if at least one assert is true
     */
    public function __construct(AssertionInterface ...$asserts)
    {
        $this->asserts = $asserts;
    }

    /**
     * Assert that at least one of the given assert is correct (OR logic)
     *
     * @param \Ecodev\Felix\Acl\Acl $acl
     * @param RoleInterface $role
     * @param ResourceInterface $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, ?RoleInterface $role = null, ?ResourceInterface $resource = null, $privilege = null)
    {
        foreach ($this->asserts as $assert) {
            if ($assert->assert($acl, $role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}

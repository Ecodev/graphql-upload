<?php

declare(strict_types=1);

namespace Ecodev\Felix\Acl\Assertion;

use Ecodev\Felix\Model\CurrentUser;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

final class IsMyself implements AssertionInterface
{
    /**
     * Assert that the user is the current user himself
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
        $user = $resource->getInstance();

        if (CurrentUser::get() && CurrentUser::get() === $user) {
            return true;
        }

        return $acl->reject('it is not himself');
    }
}

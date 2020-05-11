<?php

declare(strict_types=1);

namespace Ecodev\Felix\Acl\Assertion;

use Ecodev\Felix\Model\CurrentUser;
use Ecodev\Felix\Model\HasOwner;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

final class IsOwner implements AssertionInterface
{
    /**
     * Assert that the object belongs to the current user
     *
     * @param \Ecodev\Felix\Acl\Acl $acl
     * @param RoleInterface $role
     * @param ResourceInterface $resource
     * @param string $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        /** @var HasOwner $object */
        $object = $resource->getInstance();

        if (CurrentUser::get() && CurrentUser::get() === $object->getOwner()) {
            return true;
        }

        return $acl->reject('the object does not belong to the user');
    }
}

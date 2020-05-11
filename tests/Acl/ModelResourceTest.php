<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Acl;

use Ecodev\Felix\Acl\ModelResource;
use EcodevTests\Felix\Blog\Model\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ModelResourceTest extends TestCase
{
    public function testConstructorVariants(): void
    {
        // Constructor with pre-loaded model
        $user = new User();
        $resource = new ModelResource(User::class, $user);
        self::assertSame($user, $resource->getInstance(), 'should be able to get back model');
        self::assertSame('User#null', $resource->getName(), 'should have unique name');

        $resourceWithoutInstance = new ModelResource(User::class);
        self::assertSame('User#null', $resourceWithoutInstance->getName(), 'should have name even without instance');
    }

    public function testUnknownClassMustThrow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ModelResource('non-existing-class-name');
    }
}

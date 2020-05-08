<?php

declare(strict_types=1);

namespace EcodevTests\Felix\ORM\Query\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Ecodev\Felix\ORM\Query\Filter\AclFilter;
use EcodevTests\Felix\Blog\Model\Post;
use EcodevTests\Felix\Blog\Model\User;
use EcodevTests\Felix\Traits\TestWithEntityManager;
use PHPUnit\Framework\TestCase;

class AclFilterTest extends TestCase
{
    use TestWithEntityManager;

    public function providerFilter(): array
    {
        return [
            'users are accessible to anonymous' => [
                false,
                User::class,
                '',
            ],
            'users are accessible to any users' => [
                true,
                User::class,
                '',
            ],
            'posts are accessible to anonymous' => [
                false,
                Post::class,
                '',
            ],
            'posts are accessible to any other users' => [
                true,
                Post::class,
                'test.id IN (SELECT',
            ],
        ];
    }

    /**
     * @dataProvider providerFilter
     */
    public function testFilter(bool $anonymous, string $class, string $expected): void
    {
        $classMetadataFactory = $this->entityManager->getMetadataFactory();
        /** @var ClassMetadata $targetEntity */
        $targetEntity = $classMetadataFactory->getMetadataFor($class);
        $filter = new AclFilter($this->entityManager);

        $filter->setUser($anonymous ? null : new User());
        $actual = $filter->addFilterConstraint($targetEntity, 'test');

        if ($expected === '') {
            self::assertSame($expected, $actual);
        } else {
            self::assertStringStartsWith($expected, $actual);
        }
    }

    public function testDeactivable(): void
    {
        /** @var ClassMetadata $targetEntity */
        $targetEntity = $this->entityManager->getMetadataFactory()->getMetadataFor(Post::class);
        $filter = new AclFilter($this->entityManager);

        $this->assertNotSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'enabled by default');

        $filter->runWithoutAcl(function () use ($filter, $targetEntity): void {
            $this->assertSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'can disable');

            $filter->runWithoutAcl(function () use ($filter, $targetEntity): void {
                $this->assertSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'can disable one more time and still disabled');
            });

            $this->assertSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'enable once and still disabled');
        });

        $this->assertNotSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'enabled a second time and really enabled');
    }

    public function testDisableForever(): void
    {
        /** @var ClassMetadata $targetEntity */
        $targetEntity = $this->entityManager->getMetadataFactory()->getMetadataFor(Post::class);
        $filter = new AclFilter($this->entityManager);

        $this->assertNotSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'enabled by default');

        $filter->disableForever();
        $this->assertSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'disabled forever');

        $filter->runWithoutAcl(function () use ($filter, $targetEntity): void {
            $this->assertSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'also disabled forever anyway');
        });

        $this->assertSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'still disabled forever');
    }

    public function testExceptionWillReEnableFilter(): void
    {
        /** @var ClassMetadata $targetEntity */
        $targetEntity = $this->entityManager->getMetadataFactory()->getMetadataFor(Post::class);
        $filter = new AclFilter($this->entityManager);

        try {
            $filter->runWithoutAcl(function (): void {
                throw new \Exception();
            });
        } catch (\Exception $e) {
        }

        $this->assertNotSame('', $filter->addFilterConstraint($targetEntity, 'test'), 'enabled even after exception');
    }
}

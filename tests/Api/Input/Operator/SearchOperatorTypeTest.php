<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api\Input\Operator;

use Doctrine\ORM\Mapping\ClassMetadata;
use Ecodev\Felix\Api\Exception;
use Ecodev\Felix\Api\Input\Operator\SearchOperatorType;
use Ecodev\Felix\Testing\Api\Input\Operator\AbstractOperatorType;
use EcodevTests\Felix\Blog\Model\Post;
use EcodevTests\Felix\Blog\Model\User;
use EcodevTests\Felix\TypesTrait;
use GraphQL\Doctrine\Factory\UniqueNameFactory;
use GraphQL\Type\Definition\Type;

class SearchOperatorTypeTest extends AbstractOperatorType
{
    use TypesTrait;

    /**
     * @dataProvider providerSearch
     *
     * @param string $class
     * @param string $term
     * @param int $expectedJoinCount
     * @param string $expected
     */
    public function testSearch(string $class, string $term, int $expectedJoinCount, string $expected): void
    {
        $operator = new class($this->types, Type::string()) extends SearchOperatorType {
            protected function getSearchableFieldsWhitelist(ClassMetadata $metadata): array
            {
                return ['name', 'email', 'title'];
            }

            protected function getSearchableJoinedEntities(): array
            {
                return [Post::class => 'user'];
            }
        };

        $metadata = $this->entityManager->getClassMetadata($class);
        $unique = new UniqueNameFactory();
        $alias = 'a';
        $qb = $this->entityManager->getRepository($class)->createQueryBuilder($alias);
        $actual = $operator->getDqlCondition($unique, $metadata, $qb, $alias, 'non-used-field-name', ['value' => $term]);

        self::assertSame($expected, $actual);

        $joins = $qb->getDQLPart('join');
        self::assertCount($expectedJoinCount, $joins['a'] ?? []);
    }

    public function providerSearch(): array
    {
        return [
            'search predefined fields' => [
                User::class,
                'john',
                0,
                '(a.name LIKE :filter1 OR a.email LIKE :filter1)',
            ],
            'split words' => [
                User::class,
                'john doe',
                0,
                '(a.name LIKE :filter1 OR a.email LIKE :filter1) AND (a.name LIKE :filter2 OR a.email LIKE :filter2)',
            ],
            'trimmed split words' => [
                User::class,
                '  foo   bar   ',
                0,
                '(a.name LIKE :filter1 OR a.email LIKE :filter1) AND (a.name LIKE :filter2 OR a.email LIKE :filter2)',
            ],
            'joined entities' => [
                Post::class,
                'foo',
                1,
                '(a.title LIKE :filter1 OR user1.name LIKE :filter1 OR user1.email LIKE :filter1)',
            ],
        ];
    }

    public function testSearchOnEntityWithoutSearchableFieldMustThrow(): void
    {
        $operator = new class($this->types, Type::string()) extends SearchOperatorType {
            protected function getSearchableFieldsWhitelist(ClassMetadata $metadata): array
            {
                return [];
            }

            protected function getSearchableJoinedEntities(): array
            {
                return [];
            }
        };

        $metadata = $this->entityManager->getClassMetadata(User::class);
        $unique = new UniqueNameFactory();
        $alias = 'a';
        $qb = $this->entityManager->getRepository(User::class)->createQueryBuilder($alias);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot find fields to search on for entity EcodevTests\Felix\Blog\Model\User');
        $operator->getDqlCondition($unique, $metadata, $qb, $alias, 'non-used-field-name', ['value' => 'foo']);
    }
}

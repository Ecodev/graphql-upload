<?php

declare(strict_types=1);

namespace Ecodev\Felix\Api\Input\Operator;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Ecodev\Felix\Api\Exception;
use GraphQL\Doctrine\Definition\Operator\AbstractOperator;
use GraphQL\Doctrine\Factory\UniqueNameFactory;
use GraphQL\Type\Definition\LeafType;

abstract class SearchOperatorType extends AbstractOperator
{
    protected function getConfiguration(LeafType $leafType): array
    {
        return [
            'fields' => [
                [
                    'name' => 'value',
                    'type' => self::nonNull($leafType),
                ],
            ],
        ];
    }

    public function getDqlCondition(UniqueNameFactory $uniqueNameFactory, ClassMetadata $metadata, QueryBuilder $queryBuilder, string $alias, string $field, ?array $args): ?string
    {
        if (!$args) {
            return null;
        }

        $words = preg_split('/[[:space:]]+/', $args['value'], -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) {
            return null;
        }

        $scalarFields = $this->getSearchableFields($metadata, $alias);
        $fieldsOnJoin = $this->getSearchableFieldsOnJoin($uniqueNameFactory, $metadata, $queryBuilder, $alias);
        $allFields = array_merge($scalarFields, $fieldsOnJoin);

        return $this->buildSearchDqlCondition($uniqueNameFactory, $metadata, $queryBuilder, $allFields, $words);
    }

    abstract protected function getSearchableFieldsWhitelist(ClassMetadata $metadata): array;

    private function getSearchableFields(ClassMetadata $metadata, string $alias): array
    {
        $whitelistedFields = $this->getSearchableFieldsWhitelist($metadata);

        // Find most textual fields for the entity
        $fields = [];
        foreach ($metadata->fieldMappings as $mapping) {
            if (in_array($mapping['fieldName'], $whitelistedFields, true)) {
                $fieldName = $mapping['fieldName'];
                $field = $alias . '.' . $fieldName;

                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Map one class to one joined entity that is searchable
     *
     * This list should be kept as small as possible
     */
    abstract protected function getSearchableJoinedEntities(): array;

    /**
     * Return searchable fields from a joined entity
     *
     * This should be avoided if possible to instead only search in the original entity itself.
     */
    private function getSearchableFieldsOnJoin(UniqueNameFactory $uniqueNameFactory, ClassMetadata $metadata, QueryBuilder $queryBuilder, string $alias): array
    {
        $config = $this->getSearchableJoinedEntities();

        $fields = [];
        foreach ($config as $class => $fieldName) {
            if (is_a($metadata->getName(), $class, true)) {
                $fields = array_merge(
                    $fields,
                    $this->searchOnJoinedEntity($uniqueNameFactory, $metadata, $queryBuilder, $alias, $fieldName)
                );
            }
        }

        return $fields;
    }

    /**
     * Add a join and return searchable fields in order to search on a joined entity
     *
     * @param UniqueNameFactory $uniqueNameFactory
     * @param ClassMetadata $metadata
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $fieldName
     *
     * @return string[]
     */
    private function searchOnJoinedEntity(UniqueNameFactory $uniqueNameFactory, ClassMetadata $metadata, QueryBuilder $queryBuilder, string $alias, string $fieldName): array
    {
        $association = $metadata->getAssociationMapping($fieldName);
        $targetEntity = $association['targetEntity'];

        /** @var ClassMetadata $joinedMetadata */
        $joinedMetadata = $queryBuilder->getEntityManager()->getMetadataFactory()->getMetadataFor($targetEntity);
        $joinedAlias = $uniqueNameFactory->createAliasName($targetEntity);

        $queryBuilder->leftJoin($alias . '.' . $fieldName, $joinedAlias, Join::WITH);

        return $this->getSearchableFields($joinedMetadata, $joinedAlias);
    }

    /**
     * Return a DQL condition to search each of the words in any of the fields
     */
    private function buildSearchDqlCondition(UniqueNameFactory $uniqueNameFactory, ClassMetadata $metadata, QueryBuilder $queryBuilder, array $fields, array $words): string
    {
        if (!$fields) {
            throw new Exception('Cannot find fields to search on for entity ' . $metadata->name);
        }

        $wordWheres = [];

        foreach ($words as $i => $word) {
            $parameterName = $uniqueNameFactory->createParameterName();

            $fieldWheres = [];
            foreach ($fields as $field) {
                $fieldWheres[] = $field . ' LIKE :' . $parameterName;
            }

            if ($fieldWheres) {
                $wordWheres[] = '(' . implode(' OR ', $fieldWheres) . ')';
                $queryBuilder->setParameter($parameterName, '%' . $word . '%');
            }
        }

        return implode(' AND ', $wordWheres);
    }
}

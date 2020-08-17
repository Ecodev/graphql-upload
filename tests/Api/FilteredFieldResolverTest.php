<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Api;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\Proxy;
use Ecodev\Felix\Api\FilteredFieldResolver;
use EcodevTests\Felix\Blog\Model\User;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FilteredFieldResolverTest extends TestCase
{
    public function providerLoad(): array
    {
        $loadableClass = new class() implements Proxy {
            public function __load(): void
            {
            }

            public function __isInitialized(): bool
            {
                return true;
            }
        };

        $unLoadableClass = new class() implements Proxy {
            public function __load(): void
            {
                throw new EntityNotFoundException();
            }

            public function __isInitialized(): bool
            {
                return true;
            }
        };

        $object = new stdClass();
        $user = new User();
        $loadable = new $loadableClass();
        $unloadable = new $unLoadableClass();

        return [
            [null, null],
            [1, 1],
            ['foo', 'foo'],
            [$object, $object],
            [$user, $user],
            [$loadable, $loadable],
            [$unloadable, null],
        ];
    }

    /**
     * @dataProvider providerLoad
     *
     * @param mixed $value
     * @param mixed $expected
     */
    public function testLoad($value, $expected): void
    {
        $model = new class($value) {
            /**
             * @var mixed
             */
            private $value;

            /**
             * @param mixed $value
             */
            public function __construct($value)
            {
                $this->value = $value;
            }

            /**
             * @return mixed
             */
            public function getField()
            {
                return $this->value;
            }
        };

        $fieldDefinition = FieldDefinition::create(['name' => 'field', 'type' => Type::boolean()]);
        $resolve = new ResolveInfo($fieldDefinition, [], new ObjectType(['name' => 'foo']), [], new Schema([]), [], null, null, []);
        $resolver = new FilteredFieldResolver();
        self::assertSame($expected, $resolver($model, [], [], $resolve));
    }
}

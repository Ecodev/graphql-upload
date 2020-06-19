<?php

declare(strict_types=1);

namespace EcodevTests\Felix;

use Ecodev\Felix\Model\Model;
use Ecodev\Felix\Utility;
use EcodevTests\Felix\Blog\Model\User;
use GraphQL\Doctrine\Definition\EntityID;
use stdClass;

final class UtilityTest extends \PHPUnit\Framework\TestCase
{
    public function testGetShortClassName(): void
    {
        self::assertSame('User', Utility::getShortClassName(new User()));
        self::assertSame('User', Utility::getShortClassName(User::class));
    }

    public function testEntityIdToModel(): void
    {
        $input = [
            3 => new stdClass(),
            4 => 1,
            'model' => new User(),
            'entity' => new class() extends EntityID {
                public function __construct()
                {
                }

                public function getEntity()
                {
                    return 'real entity';
                }
            },
        ];

        $actual = Utility::entityIdToModel($input);

        $expected = $input;
        $expected['entity'] = 'real entity';

        self::assertSame($expected, $actual, 'keys and non model values should be preserved');
        self::assertNull(Utility::entityIdToModel(null));
        self::assertSame([], Utility::entityIdToModel([]));
    }

    public function testModelToId(): void
    {
        $input = [
            'entity' => new class() implements Model {
                public function getId(): ?int
                {
                    return 123456;
                }
            },
            4 => 1,
        ];

        $actual = Utility::modelToId($input);

        $expected = $input;
        $expected['entity'] = 123456;

        self::assertSame($expected, $actual, 'models must be replaced by their ids, other values should be preserved');
    }
}

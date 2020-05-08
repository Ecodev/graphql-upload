<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Model\Traits;

use Ecodev\Felix\Model\Traits\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * @var \Ecodev\Felix\Model\Image
     */
    private $image;

    protected function setUp(): void
    {
        $this->image = new class() implements \Ecodev\Felix\Model\Image {
            use Image;
        };
    }

    public function testGetPath(): void
    {
        $this->image->setFilename('photo.jpg');

        self::assertSame('photo.jpg', $this->image->getFilename());
        $appPath = realpath('.');
        $expected = $appPath . '/data/images/photo.jpg';
        self::assertSame($expected, $this->image->getPath());
    }

    public function testDimension(): void
    {
        $this->image->setWidth(123);
        $this->image->setHeight(456);

        self::assertSame(123, $this->image->getWidth());
        self::assertSame(456, $this->image->getHeight());
    }
}

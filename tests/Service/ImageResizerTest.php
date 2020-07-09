<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Service;

use Ecodev\Felix\Model\Image;
use Ecodev\Felix\Service\ImageResizer;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use PHPUnit\Framework\TestCase;

class ImageResizerTest extends TestCase
{
    /**
     * @dataProvider providerResize
     */
    public function testResize(int $wantedHeight, bool $useWebp, bool $isSvg, string $expected): void
    {
        $imagineImage = $this->createMock(ImageInterface::class);
        $imagineImage->expects(self::any())->method('thumbnail')->willReturnSelf();

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine->expects(self::any())->method('open')->willReturn($imagineImage);

        $resizer = new ImageResizer($imagine);
        $image = $this->createMock(Image::class);
        $image->expects(self::once())->method('getPath')->willReturn($isSvg ? '/felix/image.svg' : '/felix/image.png');
        $image->expects(self::any())->method('getFilename')->willReturn($isSvg ? 'image.svg' : 'image.png');
        $image->expects(self::once())->method('getHeight')->willReturn(200);
        $image->expects(self::any())->method('getMime')->willReturn($isSvg ? 'image/svg+xml' : 'image/png');

        $actual = $resizer->resize($image, $wantedHeight, $useWebp);
        self::assertStringEndsWith($expected, $actual);
    }

    public function providerResize(): array
    {
        return [
            'smaller' => [100, false, false, 'data/cache/images/image-100.jpg'],
            'smaller webp' => [100, true, false, 'data/cache/images/image-100.webp'],
            'same' => [200, false, false, '/felix/image.png'],
            'same webp' => [200, true, false, '/felix/image.png'],
            'bigger' => [300, false, false, '/felix/image.png'],
            'bigger webp' => [300, true, false, '/felix/image.png'],

            // SVG is never resized
            'svg smaller' => [100, false, true, '/felix/image.svg'],
            'svg smaller webp' => [100, true, true, '/felix/image.svg'],
            'svg same' => [200, false, true, '/felix/image.svg'],
            'svg same webp' => [200, true, true, '/felix/image.svg'],
            'svg bigger' => [300, false, true, '/felix/image.svg'],
            'svg bigger webp' => [300, true, true, '/felix/image.svg'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Action;

use Doctrine\Persistence\ObjectRepository;
use Ecodev\Felix\Action\ImageAction;
use Ecodev\Felix\Model\Image;
use Ecodev\Felix\Service\ImageResizer;
use Laminas\Diactoros\ServerRequest;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ImageActionTest extends TestCase
{
    protected function setUp(): void
    {
        // Minimal binary headers to cheat mime detection
        $virtualFileSystem = [
            'image.png' => '',
            'image-100.jpg' => "\xff\xd8\xff\xe0\x00\x10\x4a\x46\x49\x46\x00\x01\x01\x01\x00\x60",
            'image-100.webp' => 'RIFF4<..WEBPVP8',
        ];

        vfsStream::setup('felix', null, $virtualFileSystem);
    }

    public function testWillServeJpgByDefault(): void
    {
        $image = $this->createImageMock();
        $repository = $this->createRepositoryMock($image);

        $maxHeight = 100;
        $imageResizer = $this->createMock(ImageResizer::class);
        $imageResizer->expects(self::once())
            ->method('resize')
            ->with($image, $maxHeight, false)
            ->willReturn('vfs://felix/image-100.jpg');

        // A request without accept header
        $request = new ServerRequest();
        $request = $request->withAttribute('maxHeight', $maxHeight);

        $response = $this->process($repository, $imageResizer, $request);

        self::assertSame('image/jpeg', $response->getHeaderLine('content-type'));
        self::assertSame('16', $response->getHeaderLine('content-length'));
    }

    public function testWillServeWebpIfAccepted(): void
    {
        $image = $this->createImageMock();
        $repository = $this->createRepositoryMock($image);

        $maxHeight = 100;
        $imageResizer = $this->createMock(ImageResizer::class);
        $imageResizer->expects(self::once())
            ->method('resize')
            ->with($image, $maxHeight, true)
            ->willReturn('vfs://felix/image-100.webp');

        // A request specifically accepting webp images
        $request = new ServerRequest();
        $request = $request->withAttribute('maxHeight', $maxHeight)
            ->withHeader('accept', 'text/html, image/webp, */*;q=0.8');

        $response = $this->process($repository, $imageResizer, $request);

        self::assertSame('image/webp', $response->getHeaderLine('content-type'));
        self::assertSame('15', $response->getHeaderLine('content-length'));
    }

    private function process(ObjectRepository $repository, ImageResizer $imageResizer, ServerRequestInterface $request): ResponseInterface
    {
        $action = new ImageAction($repository, $imageResizer);
        $handler = $this->createMock(RequestHandlerInterface::class);

        return $action->process($request, $handler);
    }

    private function createImageMock(): Image
    {
        $image = $this->createMock(Image::class);
        $image->expects(self::once())->method('getPath')->willReturn('vfs://felix/image.png');

        return $image;
    }

    private function createRepositoryMock(Image $image): ObjectRepository
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())->method('find')->willReturn($image);

        return $repository;
    }
}

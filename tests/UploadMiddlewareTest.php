<?php

declare(strict_types=1);

namespace GraphQLTests\Upload;

use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\RequestError;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Upload\UploadMiddleware;
use GraphQL\Upload\UploadType;
use GraphQLTests\Upload\Psr7\PsrUploadedFileStub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class UploadMiddlewareTest extends TestCase
{
    /**
     * @var UploadMiddleware
     */
    private $middleware;

    public function setUp(): void
    {
        $this->middleware = new UploadMiddleware();
    }

    public function testProcess(): void
    {
        $response = new Response();
        $handler = new class($response) implements RequestHandlerInterface {
            private $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $middleware = $this->getMockBuilder(UploadMiddleware::class)
            ->setMethods(['processRequest'])
            ->getMock();

        // The request should be forward to processRequest()
        $request = new ServerRequest();
        $middleware->expects($this->once())->method('processRequest')->with($request);

        $actualResponse = $middleware->process($request, $handler);
        self::assertSame($response, $actualResponse, 'should return the mocked response');
    }

    public function testParsesMultipartRequest(): void
    {
        $query = '{my query}';
        $variables = [
            'test' => 1,
            'test2' => 2,
            'uploads' => [
                0 => null,
                1 => null,
            ],
        ];
        $map = [
            1 => ['variables.uploads.0'],
            2 => ['variables.uploads.1'],
        ];

        $file1 = new PsrUploadedFileStub('image.jpg', 'image/jpeg');
        $file2 = new PsrUploadedFileStub('foo.txt', 'text/plain');
        $files = [
            1 => $file1,
            2 => $file2,
        ];

        $request = $this->createRequest($query, $variables, $map, $files, 'op');
        $processedRequest = $this->middleware->processRequest($request);

        $variables['uploads'] = [
            0 => $file1,
            1 => $file2,
        ];

        self::assertSame('application/json', $processedRequest->getHeader('content-type')[0], 'request should have been transformed as application/json');
        self::assertSame($variables, $processedRequest->getParsedBody()['variables'], 'uploaded files should have been injected into variables');
    }

    public function testEmptyRequestIsValid(): void
    {
        $request = $this->createRequest('{my query}', [], [], [], 'op');
        $processedRequest = $this->middleware->processRequest($request);

        self::assertSame('application/json', $processedRequest->getHeader('content-type')[0], 'request should have been transformed as application/json');
        self::assertSame([], $processedRequest->getParsedBody()['variables'], 'variables should still be empty');
    }

    public function testNonMultipartRequestAreNotTouched(): void
    {
        $request = new ServerRequest();
        $processedRequest = $this->middleware->processRequest($request);

        self::assertSame($request, $processedRequest, 'request should have been transformed as application/json');
    }

    public function testEmptyRequestShouldThrows(): void
    {
        $request = new ServerRequest();
        $request = $request
            ->withHeader('content-type', ['multipart/form-data'])
            ->withParsedBody([]);

        $this->expectException(InvariantViolation::class);
        $this->expectExceptionMessage('PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got empty array');
        $this->middleware->processRequest($request);
    }

    public function testNullRequestShouldThrows(): void
    {
        $request = new ServerRequest();
        $request = $request
            ->withHeader('content-type', ['multipart/form-data'])
            ->withParsedBody(null);

        $this->expectException(InvariantViolation::class);
        $this->expectExceptionMessage('PSR-7 request is expected to provide parsed body for "multipart/form-data" requests but got null');
        $this->middleware->processRequest($request);
    }

    public function testInvalidRequestShouldThrows(): void
    {
        $request = new ServerRequest();
        $request = $request
            ->withHeader('content-type', ['multipart/form-data'])
            ->withParsedBody('foo');

        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('GraphQL Server expects JSON object or array, but got "foo"');
        $this->middleware->processRequest($request);
    }

    public function testOtherContentTypeShouldNotBeTouched(): void
    {
        $request = new ServerRequest();
        $request = $request
            ->withHeader('content-type', ['application/json'])
            ->withParsedBody('foo');

        $processedRequest = $this->middleware->processRequest($request);
        self::assertSame($request, $processedRequest);
    }

    public function testRequestWithoutMapShouldThrows(): void
    {
        $request = $this->createRequest('{my query}', [], [], [], 'op');

        // Remove the map
        $body = $request->getParsedBody();
        unset($body['map']);
        $request = $request->withParsedBody($body);

        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('The request must define a `map`');
        $this->middleware->processRequest($request);
    }

    public function testCanUploadFileWithStandardServer(): void
    {
        $query = 'mutation TestUpload($text: String, $file: Upload) {
    testUpload(text: $text, file: $file)
}';
        $variables = [
            'text' => 'foo bar',
            'file' => null,
        ];
        $map = [
            1 => ['variables.file'],
        ];
        $files = [
            1 => new PsrUploadedFileStub('image.jpg', 'image/jpeg'),
        ];

        $request = $this->createRequest($query, $variables, $map, $files, 'TestUpload');

        $processedRequest = $this->middleware->processRequest($request);

        $server = $this->createServer();

        /** @var ExecutionResult $response */
        $response = $server->executePsrRequest($processedRequest);

        $expected = ['testUpload' => 'Uploaded file was image.jpg (image/jpeg) with description: foo bar'];
        $this->assertSame($expected, $response->data);
    }

    private function createRequest(string $query, array $variables, array $map, array $files, string $operation): ServerRequestInterface
    {
        $request = new ServerRequest();
        $request = $request
            ->withMethod('POST')
            ->withHeader('content-type', ['multipart/form-data; boundary=----WebKitFormBoundarySl4GaqVa1r8GtAbn'])
            ->withParsedBody([
                'operations' => json_encode([
                    'query' => $query,
                    'variables' => $variables,
                    'operationName' => $operation,
                ]),
                'map' => json_encode($map),
            ])
            ->withUploadedFiles($files);

        return $request;
    }

    private function createServer(): StandardServer
    {
        return new StandardServer([
            'debug' => true,
            'schema' => new Schema([
                'query' => new ObjectType([
                    'name' => 'Query',
                ]),
                'mutation' => new ObjectType([
                    'name' => 'Mutation',
                    'fields' => [
                        'testUpload' => [
                            'type' => Type::string(),
                            'args' => [
                                'text' => Type::string(),
                                'file' => new UploadType(),
                            ],
                            'resolve' => function ($root, array $args): string {
                                /** @var UploadedFileInterface $file */
                                $file = $args['file'];
                                $this->assertInstanceOf(UploadedFileInterface::class, $file);

                                // Do something more interesting with the file
                                // $file->moveTo('some/folder/in/my/project');

                                return 'Uploaded file was ' . $file->getClientFilename() . ' (' . $file->getClientMediaType() . ') with description: ' . $args['text'];
                            },
                        ],
                    ],
                ]),
            ]),
        ]);
    }
}

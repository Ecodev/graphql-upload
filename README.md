# GraphQL Upload

[![Build Status](https://github.com/ecodev/graphql-upload/workflows/main/badge.svg)](https://github.com/ecodev/graphql-upload/actions)
[![Code Quality](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/?branch=master)
[![Total Downloads](https://poser.pugx.org/ecodev/graphql-upload/downloads.png)](https://packagist.org/packages/ecodev/graphql-upload)
[![Latest Stable Version](https://poser.pugx.org/ecodev/graphql-upload/v/stable.png)](https://packagist.org/packages/ecodev/graphql-upload)
[![License](https://poser.pugx.org/ecodev/graphql-upload/license.png)](https://packagist.org/packages/ecodev/graphql-upload)
[![Join the chat at https://gitter.im/Ecodev/graphql-upload](https://badges.gitter.im/Ecodev/graphql-upload.svg)](https://gitter.im/Ecodev/graphql-upload)

A [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware to support file uploads in GraphQL. It implements
[the multipart request specification](https://github.com/jaydenseric/graphql-multipart-request-spec)
for [webonyx/graphql-php](https://github.com/webonyx/graphql-php).


## Quick start

Install the library via composer:

```sh
composer require ecodev/graphql-upload
```

### Configure as middleware

In Laminas Mezzio, it would typically be in `config/routes.php` something like:

```php
use Application\Action\GraphQLAction;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use GraphQL\Upload\UploadMiddleware;

$app->post('/graphql', [
    BodyParamsMiddleware::class, 
    UploadMiddleware::class, // This is the magic
    GraphQLAction::class,
], 'graphql');
```

#### Other frameworks

This lib is an implementation of PSR-15, so it can be used with any
framework supporting PSR-15. For specific configuration instructions, refer
to your framework documentation.

If your framework does not support PSR-15 middleware, you will probably
need some kind of bridge. Again, refer to your framework for specific instructions.
Or else, you could use the direct usage below for manual integration.

### Direct usage

If you don't use middleware, it can be called directly like so:

```php
<?php

use GraphQL\Server\StandardServer;
use GraphQL\Upload\UploadMiddleware;
use Laminas\Diactoros\ServerRequestFactory;

// Create request (or get it from a framework)
$request = ServerRequestFactory::fromGlobals();
$request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));

// Process uploaded files
$uploadMiddleware = new UploadMiddleware();
$request = $uploadMiddleware->processRequest($request);

// Execute request and emits response
$server = new StandardServer(/* your config here */);
$result = $server->executePsrRequest($request);
$server->getHelper()->sendResponse($result);
```

### Usage in schema

Then you can start using in your mutations like so:

```php
<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Upload\UploadType;
use Psr\Http\Message\UploadedFileInterface;

// Build your Schema
$schema = new Schema([
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

                    // Do something with the file
                    $file->moveTo('some/folder/in/my/project');

                    return 'Uploaded file was ' . $file->getClientFilename() . ' (' . $file->getClientMediaType() . ') with description: ' . $args['text'];
                },
            ],
        ],
    ]),
]);
```

## Limitations

- It only works with PSR-7 requests. If you were not using PSR-7 yet,
[laminas-diactoros](https://github.com/laminas/laminas-diactoros) is one of many 
implementation that could be used to create PSR-7 requests.

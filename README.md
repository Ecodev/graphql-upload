# GraphQL Upload

[![Build Status](https://travis-ci.org/Ecodev/graphql-upload.svg?branch=master)](https://travis-ci.org/Ecodev/graphql-upload)
[![Code Quality](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ecodev/graphql-upload/?branch=master)
[![Total Downloads](https://poser.pugx.org/ecodev/graphql-upload/downloads.png)](https://packagist.org/packages/ecodev/graphql-upload)
[![Latest Stable Version](https://poser.pugx.org/ecodev/graphql-upload/v/stable.png)](https://packagist.org/packages/ecodev/graphql-upload)
[![License](https://poser.pugx.org/ecodev/graphql-upload/license.png)](https://packagist.org/packages/ecodev/graphql-upload)
[![Join the chat at https://gitter.im/Ecodev/graphql-upload](https://badges.gitter.im/Ecodev/graphql-upload.svg)](https://gitter.im/Ecodev/graphql-upload)

A middleware to support file uploads in GraphQL. It implements [the multipart request specification](https://github.com/jaydenseric/graphql-multipart-request-spec) for [webonyx/graphql-php](https://github.com/webonyx/graphql-php).


## Quick start

Install the library via composer:

```sh
composer require ecodev/graphql-upload
```

Configure the middleware. In Zend Expressive, it would typically be in `config/routes.php` something like:

```php
use Application\Action\GraphQLAction;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use GraphQL\Upload\UploadMiddleware;

$app->post('/graphql', [
    BodyParamsMiddleware::class, 
    UploadMiddleware::class, // This is the magic
    GraphQLAction::class,
], 'graphql');

```

And start using it:

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

- It only works with PSR-7 requests. If you were not using PSR-7 yet, [zend-diactoros](https://github.com/zendframework/zend-diactoros) is one of many implementation that could be used to create PSR-7 requests.

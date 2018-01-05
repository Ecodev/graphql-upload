<?php

declare(strict_types=1);

namespace GraphQLTests\Upload\Psr7;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class PsrResponseStub extends Response
{
    /**
     * @var ServerRequestInterface
     */
    public $request;
}

<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration;


use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class TestRequest3 extends AbstractGatewayRequest
{
    protected function getMethod(): HttpMethod
    {
        return HttpMethod::createGet();
    }

    protected function getUri(): UriInterface
    {
        return new Uri('https://www.server.com/endpoint-3');
    }

    protected function getHeaders(): array
    {
        return [];
    }

    protected function getBody(): ?StreamInterface
    {
        return Stream::create('body-3');
    }
}

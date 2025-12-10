<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Request;

use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class SimpleRequest extends AbstractGatewayRequest
{
    private HttpMethod $method;
    private UriInterface $uri;
    private ?StreamInterface $body;

    public function __construct(
        HttpMethod $method,
        UriInterface $uri,
        ?StreamInterface $body,
        RequestFactoryInterface $requestFactory
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->body = $body;
        parent::__construct($requestFactory);
    }

    protected function getMethod(): HttpMethod
    {
        return $this->method;
    }

    protected function getUri(): UriInterface
    {
        return $this->uri;
    }

    protected function getHeaders(): array
    {
        return [];
    }

    protected function getBody(): ?StreamInterface
    {
        return $this->body;
    }
}


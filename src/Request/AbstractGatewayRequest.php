<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Request;

use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

abstract class AbstractGatewayRequest implements GatewayRequestInterface
{
    private RequestFactoryInterface $psrRequestFactory;

    public function __construct(RequestFactoryInterface $psrRequestFactory)
    {
        $this->psrRequestFactory = $psrRequestFactory;
    }

    abstract protected function getMethod(): HttpMethod;

    abstract protected function getUri(): UriInterface;

    /**
     * @return string[][]
     */
    abstract protected function getHeaders(): array;

    abstract protected function getBody(): ?StreamInterface;

    final protected function createRequest(): RequestInterface
    {
        $request = $this->psrRequestFactory->createRequest(
            (string)$this->getMethod(),
            $this->getUri()
        );

        foreach ($this->getHeaders() as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        $body = $this->getBody();
        if ($body !== null) {
            $request = $request->withBody($body);
        }

        return $request;
    }

    public function toPsrRequest(): RequestInterface
    {
        return $this->createRequest();
    }

    public function getCensoredBody(): ?StreamInterface
    {
        return $this->getBody();
    }

    /**
     * @return string[][]
     */
    public function getCensoredHeaders(): array
    {
        return $this->getHeaders();
    }

    public function getCensoredUri(): UriInterface
    {
        return $this->getUri();
    }
}

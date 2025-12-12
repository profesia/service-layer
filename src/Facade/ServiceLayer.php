<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Facade;

use Closure;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Mapper\ClosureMapper;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Request\SimpleRequest;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class ServiceLayer
{
    private GatewayInterface $gateway;
    private RequestFactoryInterface $requestFactory;
    private ?ResponseDomainMapperInterface $mapper = null;

    public function __construct(
        GatewayInterface $gateway,
        RequestFactoryInterface $requestFactory
    ) {
        $this->gateway = $gateway;
        $this->requestFactory = $requestFactory;
    }

    public function withMapperClosure(Closure $mapper): self
    {
        $this->mapper = new ClosureMapper($mapper);

        return $this;
    }

    public function withResponseMapper(ResponseDomainMapperInterface $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * Execute a simple API request
     *
     * @param UriInterface         $uri
     * @param HttpMethod           $method
     * @param StreamInterface|null $body
     * @param array<string, mixed> $clientOptions
     *
     * @return DomainResponseInterface
     * @throws \Exception
     */
    public function sendRequest(
        UriInterface $uri,
        HttpMethod $method,
        ?StreamInterface $body = null,
        array $clientOptions = []
    ): DomainResponseInterface {
        $request = new SimpleRequest(
            $method,
            $uri,
            $body,
            $this->requestFactory
        );

        $response = $this->gateway->sendRequest(
            $request,
            $this->mapper,
            AdapterConfig::createFromArray($clientOptions)
        );

        $this->resetState();
        
        return $response;
    }

    private function resetState(): void
    {
        $this->mapper = null;
    }
}

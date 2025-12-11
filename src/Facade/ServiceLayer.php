<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Facade;

use Closure;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Mapper\ClosureMapper;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Request\SimpleRequest;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Simplified facade for streamlined API prototyping and testing.
 * Provides a clean interface for making HTTP requests with optional response mapping.
 */
final class ServiceLayer
{
    private GatewayInterface $gateway;
    private RequestFactoryInterface $requestFactory;
    private ?ResponseDomainMapperInterface $mapper = null;
    private ?array $clientOptions = null;

    public function __construct(
        GatewayInterface $gateway,
        RequestFactoryInterface $requestFactory
    ) {
        $this->gateway = $gateway;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Set a response mapper using a closure (builder pattern)
     *
     * @param Closure(EndpointResponseInterface): DomainResponseInterface $mapper
     * @return self
     */
    public function withMapper(Closure $mapper): self
    {
        $this->mapper = new ClosureMapper($mapper);
        return $this;
    }

    /**
     * Set a response mapper using a ResponseDomainMapperInterface implementation (builder pattern)
     *
     * @param ResponseDomainMapperInterface $mapper
     * @return self
     */
    public function withResponseMapper(ResponseDomainMapperInterface $mapper): self
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Set client options (builder pattern)
     *
     * @param array<string, mixed> $clientOptions
     * @return self
     */
    public function withClientOptions(array $clientOptions): self
    {
        $this->clientOptions = $clientOptions;
        return $this;
    }

    /**
     * Execute a simple API request
     *
     * @param UriInterface         $uri
     * @param HttpMethod           $method
     * @param StreamInterface|null $body
     * @param array<string, mixed>|null $clientOptions
     *
     * @return DomainResponseInterface
     * @throws \Exception
     */
    public function executeRequest(
        UriInterface $uri,
        HttpMethod $method,
        ?StreamInterface $body = null,
        ?array $clientOptions = null
    ): DomainResponseInterface {
        $request = new SimpleRequest(
            $method,
            $uri,
            $body,
            $this->requestFactory
        );

        // Use provided clientOptions or fall back to builder pattern options
        $options = $clientOptions ?? $this->clientOptions;
        $adapterConfig = null;
        if ($options !== null) {
            $adapterConfig = AdapterConfig::createFromArray($options);
        }

        $response = $this->gateway->sendRequest($request, $this->mapper, $adapterConfig);
        
        // Reset state after request
        $this->resetState();
        
        return $response;
    }

    /**
     * Reset the mapper and client options state
     *
     * @return void
     */
    private function resetState(): void
    {
        $this->mapper = null;
        $this->clientOptions = null;
    }
}

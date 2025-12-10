<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport;

use Closure;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Mapper\ClosureMapper;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Request\SimpleRequest;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SimpleFacade
{
    private GatewayInterface $gateway;
    private RequestFactoryInterface $requestFactory;
    private ?ResponseDomainMapperInterface $mapper = null;
    private ?array $clientOptions = null;

    public function __construct(
        ?GatewayInterface $gateway = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->requestFactory = $requestFactory ?? new Psr17Factory();
        
        if ($gateway === null) {
            $client = new Client();
            $adapter = new GuzzleAdapter(
                $client,
                AdapterConfig::createDefault()
            );
            $gatewayLogger = new CommunicationLogger($logger ?? new NullLogger());
            $gateway = new Gateway($adapter, $gatewayLogger);
        }
        
        $this->gateway = $gateway;
    }

    /**
     * Set a response mapper using a closure (builder pattern)
     *
     * @param Closure(EndpointResponseInterface): DomainResponseInterface $mapper
     * @return self
     */
    public function withMapper(Closure $mapper): self
    {
        $clone = clone $this;
        $clone->mapper = new ClosureMapper($mapper);
        return $clone;
    }

    /**
     * Set a response mapper using a ResponseDomainMapperInterface implementation (builder pattern)
     *
     * @param ResponseDomainMapperInterface $mapper
     * @return self
     */
    public function withResponseMapper(ResponseDomainMapperInterface $mapper): self
    {
        $clone = clone $this;
        $clone->mapper = $mapper;
        return $clone;
    }

    /**
     * Set client options (builder pattern)
     *
     * @param array<string, mixed> $clientOptions
     * @return self
     */
    public function withClientOptions(array $clientOptions): self
    {
        $clone = clone $this;
        $clone->clientOptions = $clientOptions;
        return $clone;
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

        return $this->gateway->sendRequest($request, $this->mapper, $adapterConfig);
    }
}


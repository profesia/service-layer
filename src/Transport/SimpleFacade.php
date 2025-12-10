<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport;

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Request\SimpleRequest;
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

        $adapterConfig = null;
        if ($clientOptions !== null) {
            $adapterConfig = AdapterConfig::createFromArray($clientOptions);
        }

        return $this->gateway->sendRequest($request, null, $adapterConfig);
    }
}


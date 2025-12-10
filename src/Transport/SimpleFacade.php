<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport;

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
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
                GuzzleAdapterConfig::createDefault()
            );
            $gatewayLogger = new CommunicationLogger($logger ?? new NullLogger());
            $gateway = new Gateway($adapter, $gatewayLogger);
        }
        
        $this->gateway = $gateway;
    }

    /**
     * Execute a simple API request
     *
     * @param UriInterface|string $uri
     * @param string              $method
     * @param StreamInterface|null $body
     *
     * @return DomainResponseInterface
     * @throws \Exception
     */
    public function executeRequest(
        UriInterface|string $uri,
        string $method,
        ?StreamInterface $body = null
    ): DomainResponseInterface {
        $httpMethod = HttpMethod::createFromString(strtoupper($method));
        
        if (is_string($uri)) {
            $uri = (new Psr17Factory())->createUri($uri);
        }
        
        $request = new class(
            $httpMethod,
            $uri,
            $body,
            $this->requestFactory
        ) extends AbstractGatewayRequest {
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
        };

        return $this->gateway->sendRequest($request);
    }
}

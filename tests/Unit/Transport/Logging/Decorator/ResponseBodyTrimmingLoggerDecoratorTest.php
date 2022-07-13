<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Transport\Logging\Decorator;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\Decorator\ResponseBodyTrimmingDecorator;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LogLevel;

class ResponseBodyTrimmingLoggerDecoratorTest extends MockeryTestCase
{
    public function testCanDecorateLoggingOfRequestAndResponsePair()
    {
        $psrFactory     = new Psr17Factory();
        $requestMethod  = HttpMethod::createPost();
        $uri            = new Uri('https://www.test.sk');
        $requestHeaders = [
            'Test' => 'test',
        ];
        $requestBody    = Stream::create('Request body');

        $gatewayRequest = (new class($psrFactory, $requestMethod, $uri, $requestBody, $requestHeaders) extends AbstractGatewayRequest {

            private array $headers;
            private HttpMethod $requestMethod;
            private UriInterface $uri;
            private StreamInterface $body;

            public function __construct(
                RequestFactoryInterface $psrRequestFactory,
                HttpMethod $requestMethod,
                UriInterface $uri,
                StreamInterface $body,
                array $headers
            ) {
                $this->requestMethod = $requestMethod;
                $this->uri           = $uri;
                $this->body          = $body;
                $this->headers       = $headers;

                parent::__construct($psrRequestFactory);
            }

            protected function getMethod(): HttpMethod
            {
                return $this->requestMethod;
            }

            protected function getUri(): UriInterface
            {
                return $this->uri;
            }

            protected function getHeaders(): array
            {
                return $this->headers;
            }

            protected function getBody(): ?StreamInterface
            {
                return $this->body;
            }

        });

        $endpointResponse = EndpointResponse::createFromComponents(
            StatusCode::createFromInteger(200),
            Stream::create('Original body'),
            [
                [
                    'Test' => 'abce',
                ],
            ]
        );
        $start            = new DateTimeImmutable();
        $stop             = new DateTimeImmutable();
        $logLevel         = LogLevel::INFO;

        /** @var GatewayLoggerInterface|MockInterface $decoratedObject */
        $decoratedObject = Mockery::mock(GatewayLoggerInterface::class);
        $decoratedObject
            ->shouldReceive('logRequestResponsePair')
            ->once()
            ->withArgs(
                function (
                    GatewayRequestInterface $suppliedRequest,
                    EndpointResponseInterface $suppliedResponse,
                    DateTimeimmutable $suppliedStart,
                    DateTimeImmutable $suppliedStop,
                    string $suppliedLevel
                ) use ($gatewayRequest, $endpointResponse, $start, $stop, $logLevel) {
                    if ($gatewayRequest !== $suppliedRequest) {
                        return false;
                    }

                    if ($start !== $suppliedStart || $stop !== $suppliedStop) {
                        return false;
                    }

                    if ($suppliedLevel !== $logLevel) {
                        return false;
                    }

                    if ($suppliedResponse->getHeaders() !== $endpointResponse->getHeaders()) {
                        return false;
                    }

                    if ($suppliedResponse->getStatusCode() !== $endpointResponse->getStatusCode()) {
                        return false;
                    }

                    $endpointResponse->getBody()->rewind();
                    $suppliedResponse->getBody()->rewind();
                    if ($endpointResponse->getBody()->getContents() !== 'Original body'
                        || $suppliedResponse->getBody()->getContents() !== ResponseBodyTrimmingDecorator::BODY_TO_REPLACE) {
                        return false;
                    }

                    return true;
                }
            );

        $decorator = new ResponseBodyTrimmingDecorator(
            $decoratedObject
        );

        $decorator->logRequestResponsePair(
            $gatewayRequest,
            $endpointResponse,
            $start,
            $stop,
            $logLevel
        );
    }

    public function testCanHandleLoggingOfException()
    {
        $psrFactory     = new Psr17Factory();
        $gatewayRequest = (new class($psrFactory) extends AbstractGatewayRequest {
            protected function getMethod(): HttpMethod
            {
                return HttpMethod::createPost();
            }

            protected function getUri(): UriInterface
            {
                return new Uri('https://test.sk');
            }

            protected function getHeaders(): array
            {
                return [
                    'Test' => 'test',
                ];
            }

            protected function getBody(): ?StreamInterface
            {
                return Stream::create('Body');
            }

        });

        $exception = new AdapterException();
        $start     = new DateTimeImmutable();
        $stop      = new DateTimeImmutable();
        $logLevel  = LogLevel::ERROR;

        /** @var GatewayLoggerInterface|MockInterface $decoratedObject */
        $decoratedObject = Mockery::mock(GatewayLoggerInterface::class);
        $decoratedObject
            ->shouldReceive('logRequestException')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    $exception,
                    $start,
                    $stop,
                    $logLevel,
                ]
            );

        $decorator = new ResponseBodyTrimmingDecorator(
            $decoratedObject
        );

        $decorator->logRequestExceptionPair(
            $gatewayRequest,
            $exception,
            $start,
            $stop,
            $logLevel
        );
    }
}

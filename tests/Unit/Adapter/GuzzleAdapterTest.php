<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleAdapterTest extends MockeryTestCase
{
    public function testCanHandleSuccessResponse(): void
    {
        $message        = 'Success';
        $requestHeaders = ['Test' => 'abcd'];

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                $requestHeaders
            );

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );


        /** @var ResponseInterface|MockInterface $response */
        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);
        $response
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                []
            );

        $stream = Stream::create($message);
        $stream->seek(0);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $psrRequest,
                    Mockery::any()
                ]
            )
            ->andReturn(
                $response
            );

        $adapter = new GuzzleAdapter(
            $client,
            GuzzleAdapterConfig::createDefault()
        );

        $adapter->send($gatewayRequest);
    }

    public function testCanHandleClientError(): void
    {
        $errorMessage   = 'Not found';
        $requestHeaders = ['Test' => 'abcd'];

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                $requestHeaders
            );

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        /** @var ResponseInterface|MockInterface $response */
        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->times(2)
            ->andReturn(

                404
            );

        $stream = Stream::create($errorMessage);
        $stream->seek(0);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn([]);

        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $psrRequest,
                    Mockery::any()
                ]
            )
            ->andThrow(
                new RequestException(
                    $errorMessage,
                    $psrRequest,
                    $response
                )
            );

        $adapter = new GuzzleAdapter(
            $client,
            GuzzleAdapterConfig::createDefault()
        );

        $actualResponse = $adapter->send($gatewayRequest);
        $this->assertEquals(StatusCode::createFromInteger(404), $actualResponse->getStatusCode());
        $this->assertEquals($errorMessage, $actualResponse->getBody()->getContents());
    }

    public function testCanHandleServerError(): void
    {
        $errorMessage = 'Internal server error';

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                []
            );

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        /** @var ResponseInterface|MockInterface $response */
        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->times(2)
            ->andReturn(500);

        $stream = Stream::create($errorMessage);
        $stream->seek(0);
        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $response
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                []
            );

        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $psrRequest,
                    Mockery::any()
                ]
            )
            ->andThrow(
                new RequestException(
                    $errorMessage,
                    $psrRequest,
                    $response
                )
            );

        $adapter = new GuzzleAdapter(
            $client,
            GuzzleAdapterConfig::createDefault()
        );

        $actualResponse = $adapter->send($gatewayRequest);
        $this->assertEquals(StatusCode::createFromInteger(500), $actualResponse->getStatusCode());
        $this->assertEquals($errorMessage, $actualResponse->getBody()->getContents());
    }

    public function testCanHandleExceptionWithoutResponse(): void
    {
        $message = 'Error message';
        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                []
            );

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $psrRequest,
                    Mockery::any()
                ]
            )
            ->andThrow(
                new RequestException(
                    $message,
                    $psrRequest
                )
            );

        $adapter = new GuzzleAdapter(
            $client,
            GuzzleAdapterConfig::createDefault()
        );

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage($message);
        $adapter->send($gatewayRequest);
    }

    public function testCanHandleCommunicationError(): void
    {
        $errorMessage = 'Test message';

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                []
            );

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $psrRequest,
                    Mockery::any()
                ]
            )
            ->andThrow(
                new ConnectException(
                    $errorMessage,
                    $psrRequest
                )
            );


        $adapter = new GuzzleAdapter(
            $client,
            GuzzleAdapterConfig::createDefault()
        );

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage($errorMessage);
        $adapter->send($gatewayRequest);
    }

    public function testCanMergeConfig(): void
    {
        $requestHeaders = [
            'Test' => 'abcd'
        ];

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                $requestHeaders
            );

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        $rawConfig = [
            RequestOptions::TIMEOUT => 0.5,
            RequestOptions::VERIFY  => false,
            RequestOptions::AUTH    => [
                'login',
                'password'
            ]
        ];

        /** @var ResponseInterface|MockInterface $psrResponse */
        $psrResponse = Mockery::mock(ResponseInterface::class);
        $psrResponse
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $stream = Stream::create('Response body');
        $stream->seek(0);
        $psrResponse
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);
        $psrResponse
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                []
            );

        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $psrRequest,
                    $rawConfig + [RequestOptions::HEADERS => $requestHeaders]
                ]
            )
            ->andReturn(
                $psrResponse
            );

        $adapter = new GuzzleAdapter(
            $client,
            GuzzleAdapterConfig::createDefault()
        );

        $overrideConfig = GuzzleAdapterConfig::createFromArray(
            $rawConfig
        );

        $adapter->send(
            $gatewayRequest,
            $overrideConfig
        );
    }
}

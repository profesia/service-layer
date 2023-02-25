<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Transport;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\ErrorResponse;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\Gateway;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Log\LogLevel;
use Exception;

class GatewayTest extends MockeryTestCase
{

    /**
     * @group request-gateway
     */
    public function testCanOverrideAdapter(): void
    {
        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);

        $statusCode   = StatusCode::createFromInteger(201);
        $responseBody = Stream::create('{"test": "ok"}');

        $expectedResponse = EndpointResponse::createFromComponents(
            $statusCode,
            $responseBody,
            []
        );

        /** @var AdapterInterface|MockInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter
            ->shouldNotReceive('send');

        /** @var GatewayLoggerInterface|MockInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);
        $logger
            ->shouldReceive('logRequestResponsePair')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    $expectedResponse,
                    Mockery::any(),
                    Mockery::any(),
                    LogLevel::INFO,
                ]
            );

        $gateway = new Gateway(
            $adapter,
            $logger
        );


        /** @var MockInterface|AdapterInterface $adapterOverride */
        $adapterOverride = Mockery::mock(AdapterInterface::class);
        $adapterOverride
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    null,
                ]
            )->andReturn(
                $expectedResponse
            );

        $gateway->viaAdapter($adapterOverride);
        $actualResponse = $gateway->sendRequest($gatewayRequest);

        $this->assertEquals(
            SimpleResponse::createFromStatusCodeAndStream(
                $statusCode,
                $responseBody
            ),
            $actualResponse
        );
    }

    /**
     * @group request-gateway
     */
    public function testCanOverrideLogger(): void
    {
        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);

        $statusCode   = StatusCode::createFromInteger(201);
        $responseBody = Stream::create('{"test": "ok"}');

        $expectedResponse = EndpointResponse::createFromComponents(
            $statusCode,
            $responseBody,
            []
        );

        /** @var AdapterInterface|MockInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    null,
                ]
            )->andReturn(
                $expectedResponse
            );

        /** @var GatewayLoggerInterface|MockInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);
        $logger
            ->shouldNotReceive('logRequestResponsePair');

        /** @var GatewayLoggerInterface|MockInterface $loggerOverride */
        $loggerOverride = Mockery::mock(GatewayLoggerInterface::class);
        $loggerOverride
            ->shouldReceive('logRequestResponsePair')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    $expectedResponse,
                    Mockery::any(),
                    Mockery::any(),
                    LogLevel::INFO,
                ]
            );

        $gateway = new Gateway(
            $adapter,
            $logger
        );

        $gateway->useLogger($loggerOverride);
        $actualResponse = $gateway->sendRequest($gatewayRequest);

        $this->assertEquals(
            SimpleResponse::createFromStatusCodeAndStream(
                $statusCode,
                $responseBody
            ),
            $actualResponse
        );
    }

    /**
     * @group request-gateway
     */
    public function testCanMapResponseToDomain(): void
    {
        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);

        $statusCode = StatusCode::createFromInteger(201);
        $response   = Stream::create('{"test": "ok"}');

        /** @var EndpointResponseInterface|MockInterface $expectedResponse */
        $expectedResponse = Mockery::mock(EndpointResponseInterface::class);
        $expectedResponse
            ->shouldReceive('isSuccessful')
            ->times(2)
            ->andReturn(true);
        $expectedResponse
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(
                $statusCode
            );

        /** @var AdapterInterface|MockInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    null,
                ]
            )
            ->andReturn($expectedResponse);

        /** @var GatewayLoggerInterface|MockInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);
        $logger
            ->shouldReceive('logRequestResponsePair')
            ->once()
            ->withArgs(
                [
                    $gatewayRequest,
                    $expectedResponse,
                    Mockery::any(),
                    Mockery::any(),
                    LogLevel::INFO,
                ]
            );

        $mappedResponse = SimpleResponse::createFromStatusCodeAndStream(
            $statusCode,
            $response
        );

        $argumentCallback = function (EndpointResponseInterface $argument) {
            if ($argument->getStatusCode()->equalsWithInt(201) === false) {
                return false;
            }

            if ($argument->isSuccessful() !== true) {
                return false;
            }

            return true;
        };

        $gateway = new Gateway(
            $adapter,
            $logger
        );

        /** @var ResponseDomainMapperInterface|MockInterface $mapper */
        $mapper = Mockery::mock(ResponseDomainMapperInterface::class);
        $mapper
            ->shouldReceive('mapToDomain')
            ->times(1)
            ->withArgs(
                $argumentCallback
            )->andReturn(
                $mappedResponse
            );

        $actualResponse = $gateway->sendRequest(
            $gatewayRequest,
            $mapper
        );
        $this->assertEquals($mappedResponse, $actualResponse);
    }

    /**
     * @group request-gateway
     */
    public function testCanHandleClientException(): void
    {
        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);

        $message          = 'Error during communication';
        $exception        = new AdapterException($message);
        $expectedResponse = new ErrorResponse($exception);

        /** @var AdapterInterface|MockInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter
            ->shouldReceive('send')
            ->times(1)
            ->withArgs(
                [
                    $gatewayRequest,
                    null,
                ]
            )
            ->andThrow($exception);

        /** @var GatewayLoggerInterface|MockInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);
        $logger
            ->shouldReceive('logRequestExceptionPair')
            ->times(1)
            ->withArgs(
                [
                    $gatewayRequest,
                    $exception,
                    Mockery::any(),
                    Mockery::any(),
                    LogLevel::CRITICAL,
                ]
            );

        $gateway = new Gateway(
            $adapter,
            $logger
        );

        $actualResponse = $gateway->sendRequest(
            $gatewayRequest
        );

        $this->assertEquals($expectedResponse, $actualResponse);
        $this->assertEquals($message, $actualResponse->getResponseBody());
        $this->assertFalse($actualResponse->isSuccessful());
    }

    public function testCanHandleAnyException(): void
    {
        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);

        $message          = 'Error during communication';
        $exception        = new Exception($message);

        /** @var AdapterInterface|MockInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter
            ->shouldReceive('send')
            ->times(1)
            ->withArgs(
                [
                    $gatewayRequest,
                    null,
                ]
            )
            ->andThrow($exception);

        /** @var GatewayLoggerInterface|MockInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);
        $logger
            ->shouldReceive('logRequestExceptionPair')
            ->times(1)
            ->withArgs(
                [
                    $gatewayRequest,
                    $exception,
                    Mockery::any(),
                    Mockery::any(),
                    LogLevel::CRITICAL,
                ]
            );

        $gateway = new Gateway(
            $adapter,
            $logger
        );

        $this->expectExceptionObject($exception);
        $gateway->sendRequest(
            $gatewayRequest
        );
    }
}

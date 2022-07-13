<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Transport\Logging;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Profesia\ServiceLayer\Transport\Logging\Helper\TimeDiffHelper;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class CommunicationLoggerTest extends MockeryTestCase
{
    public function testCanLogRequestResponsePar()
    {
        $requestHttpMethod = 'POST';
        $requestUri        = new Uri('https://test-1.com');
        $requestHeaders    = [
            'Test' => 'header 1',
        ];

        $requestBodyContents = 'Request body';
        /** @var MockInterface|StreamInterface $requestBody */
        $requestBody = Mockery::mock(StreamInterface::class);
        $requestBody
            ->shouldReceive('isSeekable')
            ->times(2)
            ->andReturn(
                true
            );
        $requestBody
            ->shouldReceive('rewind')
            ->times(2);
        $requestBody
            ->shouldReceive('getContents')
            ->once()
            ->andReturn(
                $requestBodyContents
            );

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getMethod')
            ->once()
            ->andReturn(
                $requestHttpMethod
            );

        /** @var MockInterface|GatewayRequestInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('getCensoredUri')
            ->once()
            ->andReturn(
                $requestUri
            );

        $gatewayRequest
            ->shouldReceive('getCensoredHeaders')
            ->once()
            ->andReturn(
                $requestHeaders
            );

        $gatewayRequest
            ->shouldReceive('getCensoredBody')
            ->once()
            ->andReturn(
                $requestBody
            );

        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        $responseStatusCode = StatusCode::createFromInteger(200);
        $responseHeaders    = [
            'response' => 'test'
        ];


        $responseBodyContents = 'Response body';
        /** @var MockInterface|StreamInterface $responseBody */
        $responseBody = Mockery::mock(StreamInterface::class);
        $responseBody
            ->shouldReceive('isSeekable')
            ->times(2)
            ->andReturn(
                true
            );

        $responseBody
            ->shouldReceive('rewind')
            ->times(2);

        $responseBody
            ->shouldReceive('getContents')
            ->once()
            ->andReturn(
                $responseBodyContents
            );

        /** @var EndpointResponseInterface|MockInterface $endpointResponse */
        $endpointResponse = Mockery::mock(EndpointResponseInterface::class);
        $endpointResponse
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(
                $responseStatusCode
            );

        $endpointResponse
            ->shouldReceive('getHeaders')
            ->once()
            ->andReturn(
                $responseHeaders
            );

        $endpointResponse
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(
                $responseBody
            );

        $logLevel = LogLevel::INFO;
        $start    = new DateTimeImmutable();
        $stop     = new DateTimeImmutable();

        /** @var LoggerInterface|MockInterface $communicationLogger */
        $communicationLogger = Mockery::mock(LoggerInterface::class);
        $communicationLogger
            ->shouldReceive('log')
            ->once()
            ->withArgs(
                [
                    $logLevel,
                    "{$requestHttpMethod}: {$requestUri}",
                    [
                        'Request'      => [
                            'Headers' => $requestHeaders,
                            'Body'    => $requestBodyContents,
                        ],
                        'Response'     => [
                            'Http Code' => $responseStatusCode->toString(),
                            'Headers'   => $responseHeaders,
                            'Body'      => $responseBodyContents,
                        ],
                        'Elapsed Time' => TimeDiffHelper::calculateDiffInMicroseconds($start, $stop)
                    ]
                ]
            );

        $logger = new CommunicationLogger(
            $communicationLogger
        );

        $logger->logRequestResponsePair(
            $gatewayRequest,
            $endpointResponse,
            $start,
            $stop,
            $logLevel
        );
        $this->assertTrue(true);
    }

    public function testCanLogRequestException()
    {
        $requestHttpMethod = 'GET';
        $requestUri        = new Uri('https://test-2.com');
        $requestHeaders    = [
            'Test' => 'header 2',
        ];

        $requestBodyString = 'Request Body';
        /** @var MockInterface|StreamInterface $requestBody */
        $requestBody = Mockery::mock(StreamInterface::class);
        $requestBody
            ->shouldReceive('isSeekable')
            ->times(2)
            ->andReturn(
                true
            );

        $requestBody
            ->shouldReceive('rewind')
            ->times(2);
        $requestBody
            ->shouldReceive('getContents')
            ->once()
            ->andReturn(
                $requestBodyString
            );

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class);
        $psrRequest
            ->shouldReceive('getMethod')
            ->once()
            ->andReturn(
                $requestHttpMethod
            );

        /** @var MockInterface|GatewayRequestInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('getCensoredUri')
            ->once()
            ->andReturn(
                $requestUri
            );

        $gatewayRequest
            ->shouldReceive('getCensoredHeaders')
            ->once()
            ->andReturn(
                $requestHeaders
            );

        $gatewayRequest
            ->shouldReceive('getCensoredBody')
            ->once()
            ->andReturn(
                $requestBody
            );

        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->once()
            ->andReturn(
                $psrRequest
            );

        $logLevel = LogLevel::ERROR;
        $start    = new DateTimeImmutable();
        $stop     = new DateTimeImmutable();

        $exceptionMessage = 'Test exception';
        $exception        = new AdapterException(
            $exceptionMessage
        );


        /** @var LoggerInterface|MockInterface $communicationLogger */
        $communicationLogger = Mockery::mock(LoggerInterface::class);
        $communicationLogger
            ->shouldReceive('log')
            ->once()
            ->withArgs(
                [
                    $logLevel,
                    "{$requestHttpMethod}: {$requestUri}",
                    [
                        'Request'      => [
                            'Headers' => $requestHeaders,
                            'Body'    => $requestBodyString,
                        ],
                        'Response'     => [
                            'Body' => $exceptionMessage,
                        ],
                        'Elapsed Time' => TimeDiffHelper::calculateDiffInMicroseconds($start, $stop)
                    ]
                ]
            );

        $logger = new CommunicationLogger(
            $communicationLogger
        );

        $logger->logRequestExceptionPair(
            $gatewayRequest,
            $exception,
            $start,
            $stop,
            $logLevel
        );
        $this->assertTrue(true);
    }
}

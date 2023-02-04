<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Integration\Transport\Logging;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Test\Integration\TestException;
use Profesia\ServiceLayer\Test\Integration\TestRequest1;
use Profesia\ServiceLayer\Test\Integration\TestRequest2;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use DateTimeImmutable;

class CommunicationLoggerTest extends TestCase
{
    public function testWillLeftRequestAndResponseStreamsIntact(): void
    {
        $psrLogger = new NullLogger();
        $logger    = new CommunicationLogger(
            $psrLogger
        );

        $responseBody = 'response-body-1';
        $response     = EndpointResponse::createFromComponents(
            StatusCode::createFromInteger(200),
            Stream::create($responseBody),
            []
        );
        $request      = new TestRequest1(
            new Psr17Factory()
        );

        $logger->logRequestResponsePair(
            $request,
            $response,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            LogLevel::INFO
        );

        $this->assertEquals($responseBody, (string)$response->getBody());
        $this->assertEquals('body-1', (string)$request->getCensoredBody());
    }

    public function testWillLeftRequestStreamsIntactOnException(): void
    {
        $psrLogger = new NullLogger();
        $logger    = new CommunicationLogger(
            $psrLogger
        );

        $request      = new TestRequest2(
            new Psr17Factory()
        );
        $exceptionMessage = 'test';
        $logger->logRequestExceptionPair(
            $request,
            new TestException($exceptionMessage),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            LogLevel::INFO
        );

        $this->assertEquals('body-2', (string)$request->getCensoredBody());
    }
}

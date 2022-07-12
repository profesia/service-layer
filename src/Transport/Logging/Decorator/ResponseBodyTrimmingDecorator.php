<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging\Decorator;

use DateTimeImmutable;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\Exception\ServiceLayerException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;

final class ResponseBodyTrimmingDecorator implements GatewayLoggerInterface
{
    public const BODY_TO_REPLACE = 'Body was trimmed by ServiceLayer library';

    private GatewayLoggerInterface $decoratedObject;

    public function __construct(GatewayLoggerInterface $decoratedObject)
    {
        $this->decoratedObject = $decoratedObject;
    }

    public function logRequestResponsePair(
        GatewayRequestInterface $request,
        EndpointResponseInterface $response,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        $newResponse = EndpointResponse::createFromComponents(
            $response->getStatusCode(),
            Stream::create(
                self::BODY_TO_REPLACE
            ),
            $response->getHeaders()
        );

        $this->decoratedObject->logRequestResponsePair($request, $newResponse, $start, $stop, $logLevel);
    }

    public function logRequestExceptionPair(
        GatewayRequestInterface $request,
        ServiceLayerException $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        $this->decoratedObject->logRequestExceptionPair($request, $exception, $start, $stop, $logLevel);
    }
}

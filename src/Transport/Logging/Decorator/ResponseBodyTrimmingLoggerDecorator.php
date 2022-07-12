<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging\Decorator;

use DateTimeImmutable;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\Exception\ServiceLayerException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;

final class ResponseBodyTrimmingLoggerDecorator implements RequestGatewayLoggerInterface
{
    public const BODY_TO_REPLACE = 'Body was trimmed by ServiceLayer library';

    private RequestGatewayLoggerInterface $decoratedObject;

    public function __construct(RequestGatewayLoggerInterface $decoratedObject)
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

    public function logRequestException(
        GatewayRequestInterface $request,
        ServiceLayerException $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        $this->decoratedObject->logRequestException($request, $exception, $start, $stop, $logLevel);
    }
}

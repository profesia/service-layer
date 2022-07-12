<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging;

use DateTimeImmutable;
use Profesia\ServiceLayer\Exception\ServiceLayerException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;

interface RequestGatewayLoggerInterface
{
    public function logRequestResponsePair(
        GatewayRequestInterface $request,
        EndpointResponseInterface $response,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void;

    public function logRequestException(
        GatewayRequestInterface $request,
        ServiceLayerException $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void;
}

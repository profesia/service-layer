<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging;

use DateTimeImmutable;
use Exception;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;

interface GatewayLoggerInterface
{
    public function logRequestResponsePair(
        GatewayRequestInterface $request,
        EndpointResponseInterface $response,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void;

    public function logRequestExceptionPair(
        GatewayRequestInterface $request,
        Exception $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void;
}

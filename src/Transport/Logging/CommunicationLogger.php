<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging;

use DateTimeImmutable;
use Exception;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\Helper\TimeDiffHelper;
use Psr\Log\LoggerInterface;

final class CommunicationLogger implements GatewayLoggerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $communicationLogger)
    {
        $this->logger = $communicationLogger;
    }

    public function logRequestResponsePair(
        GatewayRequestInterface $request,
        EndpointResponseInterface $response,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        $psrRequest  = $request->toPsrRequest();
        $requestBody = $request->getCensoredBody();
        $message     = "{$psrRequest->getMethod()}: {$request->getCensoredUri()}";
        $stackTrace  = [
            'Request'      => [
                'Headers' => $request->getCensoredHeaders(),
            ],
            'Response'     => [
                'Http Code' => $response->getStatusCode()->toString(),
                'Headers'   => $response->getHeaders(),
            ],
            'Elapsed Time' => TimeDiffHelper::calculateDiffInMicroseconds($start, $stop),
        ];

        if ($requestBody !== null) {
            $stackTrace['Request']['Body'] = (string)$requestBody;
            if ($requestBody->isSeekable()) {
                $requestBody->rewind();
            }
        }

        $responseBody                   = $response->getBody();
        $stackTrace['Response']['Body'] = (string)$responseBody;
        if ($responseBody->isSeekable()) {
            $responseBody->rewind();
        }

        $this->logger->log($logLevel, $message, $stackTrace);
    }

    public function logRequestExceptionPair(
        GatewayRequestInterface $request,
        Exception $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        $psrRequest  = $request->toPsrRequest();
        $requestBody = $request->getCensoredBody();
        $message     = "{$psrRequest->getMethod()}: {$request->getCensoredUri()}";
        $stackTrace  = [
            'Request'      => [
                'Headers' => $request->getCensoredHeaders(),
            ],
            'Response'     => [
                'Body' => $exception->getMessage(),
            ],
            'Elapsed Time' => TimeDiffHelper::calculateDiffInMicroseconds($start, $stop),
        ];

        if ($requestBody !== null) {
            $stackTrace['Request']['Body'] = (string)$requestBody;
            if ($requestBody->isSeekable()) {
                $requestBody->rewind();
            }
        }

        $this->logger->log($logLevel, $message, $stackTrace);
    }
}

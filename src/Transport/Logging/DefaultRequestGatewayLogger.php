<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging;

use DateTimeImmutable;
use Profesia\ServiceLayer\Exception\ServiceLayerException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\Helper\TimeDiffHelper;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Psr\Log\LoggerInterface;

final class DefaultRequestGatewayLogger implements RequestGatewayLoggerInterface
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
            if ($requestBody->isSeekable()) {
                $requestBody->rewind();
            }

            $stackTrace['Request']['Body'] = $requestBody->getContents();
            if ($requestBody->isSeekable()) {
                $requestBody->rewind();
            }
        }

        $responseBody = $response->getBody();
        if ($responseBody->isSeekable()) {
            $responseBody->rewind();
        }

        $stackTrace['Response']['Body'] = $responseBody->getContents();
        if ($responseBody->isSeekable()) {
            $responseBody->rewind();
        }

        $this->logger->log($logLevel, $message, $stackTrace);
    }

    public function logRequestException(
        GatewayRequestInterface $request,
        ServiceLayerException $exception,
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
            if ($requestBody->isSeekable()) {
                $requestBody->rewind();
            }

            $stackTrace['Request']['Body'] = $requestBody->getContents();
            if ($requestBody->isSeekable()) {
                $requestBody->rewind();
            }
        }

        $this->logger->log($logLevel, $message, $stackTrace);
    }
}

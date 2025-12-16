<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport;

use DateTimeImmutable;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Exception\ServiceLayerException;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Response\Domain\ErrorResponse;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Psr\Log\LogLevel;
use Exception;

final class Gateway implements GatewayInterface
{
    private AdapterInterface $adapter;
    private ?AdapterInterface $oneTimeAdapter = null;
    private GatewayLoggerInterface $gatewayLogger;
    private ?GatewayLoggerInterface $oneTimeGatewayLogger = null;

    public function __construct(
        AdapterInterface $adapter,
        GatewayLoggerInterface $gatewayLogger
    ) {
        $this->adapter       = $adapter;
        $this->gatewayLogger = $gatewayLogger;
    }

    public function viaAdapter(AdapterInterface $adapter): self
    {
        $this->oneTimeAdapter = $adapter;

        return $this;
    }

    public function useLogger(GatewayLoggerInterface $logger): self
    {
        $this->oneTimeGatewayLogger = $logger;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigInterface $adapterConfigOverride = null
    ): DomainResponseInterface {
        $startTime = new DateTimeImmutable();
        $logger    = $this->getLogger();
        $adapter   = $this->getAdapter();
        $this->resetOneTimeConfig();

        try {
            $endpointResponse = $adapter->send($gatewayRequest, $adapterConfigOverride);

            $logger->logRequestResponsePair(
                $gatewayRequest,
                $endpointResponse,
                $startTime,
                new DateTimeImmutable(),
                $endpointResponse->isSuccessful() ? LogLevel::INFO : LogLevel::ERROR
            );

            if ($mapper !== null) {
                return $mapper->mapToDomain($endpointResponse);
            }

            return SimpleResponse::createFromEndpointResponse($endpointResponse);
        } catch (ServiceLayerException $e) {
            $logger->logRequestExceptionPair(
                $gatewayRequest,
                $e,
                $startTime,
                new DateTimeImmutable(),
                LogLevel::CRITICAL
            );

            return new ErrorResponse($e);
        } catch (Exception $e1) {
            $logger->logRequestExceptionPair(
                $gatewayRequest,
                $e1,
                $startTime,
                new DateTimeImmutable(),
                LogLevel::CRITICAL
            );

            throw $e1;
        }
    }

    private function getLogger(): GatewayLoggerInterface
    {
        if ($this->oneTimeGatewayLogger !== null) {
            return $this->oneTimeGatewayLogger;
        }

        return $this->gatewayLogger;
    }

    private function getAdapter(): AdapterInterface
    {
        if ($this->oneTimeAdapter !== null) {
            return $this->oneTimeAdapter;
        }

        return $this->adapter;
    }

    private function resetOneTimeConfig(): void
    {
        $this->oneTimeAdapter       = null;
        $this->oneTimeGatewayLogger = null;
    }
}

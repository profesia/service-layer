<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Registry\Config;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AbstractAdapterConfig;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;

final class RequestConfig
{
    public function __construct(
        private ?GatewayRequestInterface $request = null,
        private ?AbstractAdapterConfig $configOverride = null,
        private ?AdapterInterface $adapterOverride = null,
        private ?GatewayLoggerInterface $loggerOverride = null,
        private ?GatewayInterface $gatewayOverride = null,
        private ?ResponseDomainMapperInterface $mapper = null
    )
    {
    }

    public function getRequest(): ?GatewayRequestInterface
    {
        return $this->request;
    }

    public function getConfigOverride(): ?AbstractAdapterConfig
    {
        return $this->configOverride;
    }

    public function getAdapterOverride(): ?AdapterInterface
    {
        return $this->adapterOverride;
    }

    public function hasOverriddenAdapter(): bool
    {
        return $this->adapterOverride !== null;
    }

    public function getLoggerOverride(): ?GatewayLoggerInterface
    {
        return $this->loggerOverride;
    }

    public function hasOverriddenLogger(): bool
    {
        return $this->loggerOverride !== null;
    }

    public function getGatewayOverride(): ?GatewayInterface
    {
        return $this->gatewayOverride;
    }

    public function hasOverriddenGateway(): bool
    {
        return $this->gatewayOverride !== null;
    }

    public function getMapper(): ?ResponseDomainMapperInterface
    {
        return $this->mapper;
    }
}
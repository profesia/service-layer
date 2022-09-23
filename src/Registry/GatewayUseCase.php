<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Registry;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Registry\Exception\BadStateException;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;

final class GatewayUseCase
{
    private ?GatewayRequestInterface $request = null;
    private GatewayInterface $defaultGateway;
    private ?GatewayInterface $gatewayOverride = null;
    private ?ResponseDomainMapperInterface $mapper;
    private ?ResponseDomainMapperInterface $mapperOverride = null;
    private ?AdapterConfigInterface $adapterOverrideConfigBuilder;
    private ?GatewayLoggerInterface $loggerOverride = null;
    private ?AdapterInterface $adapterOverride = null;

    public function __construct(
        GatewayInterface $defaultGateway,
        ?GatewayRequestInterface $request = null,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigInterface $adapterOverrideConfigBuilder = null
    ) {
        $this->request                      = $request;
        $this->defaultGateway               = $defaultGateway;
        $this->mapper                       = $mapper;
        $this->adapterOverrideConfigBuilder = $adapterOverrideConfigBuilder;
    }

    public function throughGatewayOverride(?GatewayInterface $gatewayOverride = null): self
    {
        $this->gatewayOverride = $gatewayOverride;

        return $this;
    }

    public function viaAdapter(?AdapterInterface $adapter = null): self
    {
        $this->adapterOverride = $adapter;

        return $this;
    }

    public function useLogger(?GatewayLoggerInterface $logger = null): self
    {
        $this->loggerOverride = $logger;

        return $this;
    }

    public function setRequestToSend(GatewayRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function withMapper(ResponseDomainMapperInterface $mapperOverride): self
    {
        $this->mapperOverride = $mapperOverride;

        return $this;
    }

    /**
     * @return DomainResponseInterface
     * @throws BadStateException
     */
    public function performRequest(): DomainResponseInterface
    {
        if ($this->request === null) {
            throw new BadStateException('Request to send was not set. Before invoking `performRequest` you have to set request first');
        }

        return $this->constructFinalGatewayToUse()
            ->sendRequest(
                $this->request,
                $this->getMapperToUse(),
                $this->adapterOverrideConfigBuilder
            );
    }

    private function constructFinalGatewayToUse(): GatewayInterface
    {
        $gatewayToUse = ($this->gatewayOverride !== null) ? $this->gatewayOverride: $this->defaultGateway;
        if ($this->adapterOverride !== null) {
            $gatewayToUse->viaAdapter($this->adapterOverride);
        }

        if ($this->loggerOverride !== null) {
            $gatewayToUse->useLogger($this->loggerOverride);
        }

        return $gatewayToUse;
    }

    private function getMapperToUse(): ?ResponseDomainMapperInterface
    {
        if ($this->mapperOverride !== null) {
            return $this->mapperOverride;
        }

        return $this->mapper;
    }
}

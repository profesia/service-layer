<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Registry;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigBuilderInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Registry\Exception\BadStateException;
use Profesia\ServiceLayer\Response\Domain\GatewayDomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;

final class GatewayUseCase
{
    private ?GatewayRequestInterface $request = null;
    private GatewayInterface $defaultGateway;
    private ?GatewayInterface $gatewayOverride = null;
    private ?ResponseDomainMapperInterface $mapper;
    private ?AdapterConfigBuilderInterface $adapterOverrideConfigBuilder;
    private ?RequestGatewayLoggerInterface $loggerOverride = null;
    private ?AdapterInterface $adapterOverride = null;

    public function __construct(
        GatewayInterface $defaultGateway,
        ?GatewayRequestInterface $request = null,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigBuilderInterface $adapterOverrideConfigBuilder = null
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

    public function useLogger(?RequestGatewayLoggerInterface $logger = null): self
    {
        $this->loggerOverride = $logger;

        return $this;
    }

    public function setRequestToSend(GatewayRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return GatewayDomainResponseInterface
     * @throws BadStateException
     */
    public function performRequest(): GatewayDomainResponseInterface
    {
        if ($this->request === null) {
            throw new BadStateException('Request to send was not set. Before invoking `performRequest` you have to set request first');
        }

        return $this->constructFinalGatewayToUse()
            ->sendRequest(
                $this->request,
                $this->mapper,
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
}

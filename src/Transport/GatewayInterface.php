<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;

interface GatewayInterface
{
    public function viaAdapter(AdapterInterface $adapter): self;
    public function useLogger(RequestGatewayLoggerInterface $logger): self;

    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigInterface $adapterOverrideConfigBuilder = null
    ): DomainResponseInterface;
}

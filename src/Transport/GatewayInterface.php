<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigBuilderInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\GatewayDomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;

interface GatewayInterface
{
    public function viaAdapter(AdapterInterface $adapter): self;
    public function useLogger(RequestGatewayLoggerInterface $logger): self;

    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigBuilderInterface $adapterOverrideConfigBuilder = null
    ): GatewayDomainResponseInterface;
}

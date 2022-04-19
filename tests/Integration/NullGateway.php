<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigBuilderInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\GatewayDomainResponseInterface;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;

final class NullGateway implements GatewayInterface
{
    public function viaAdapter(AdapterInterface $adapter): GatewayInterface
    {
        return $this;
    }

    public function useLogger(RequestGatewayLoggerInterface $logger): GatewayInterface
    {
        return $this;
    }

    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigBuilderInterface $adapterOverrideConfigBuilder = null
    ): GatewayDomainResponseInterface {
        $psrRequest = $gatewayRequest->toPsrRequest();
        return SimpleResponse::createFromStatusCodeAndStream(
            StatusCode::createFromInteger(200),
            $psrRequest->getBody()
        );
    }

}

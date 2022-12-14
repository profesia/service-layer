<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;

final class NullGateway implements GatewayInterface
{
    public function viaAdapter(AdapterInterface $adapter): GatewayInterface
    {
        return $this;
    }

    public function useLogger(GatewayLoggerInterface $logger): GatewayInterface
    {
        return $this;
    }

    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigInterface $adapterOverrideConfigBuilder = null
    ): DomainResponseInterface {
        $psrRequest = $gatewayRequest->toPsrRequest();
        return SimpleResponse::createFromStatusCodeAndStream(
            StatusCode::createFromInteger(200),
            $psrRequest->getBody()
        );
    }

}

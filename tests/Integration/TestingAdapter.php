<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigBuilderInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;

class TestingAdapter implements AdapterInterface
{
    public function send(GatewayRequestInterface $request, ?AdapterConfigBuilderInterface $configOverrideBuilder = null): EndpointResponseInterface
    {
        $psrRequest = $request->toPsrRequest();
        return EndpointResponse::createFromComponents(
            StatusCode::createFromInteger(200),
            $psrRequest->getBody(),
            []
        );
    }
}

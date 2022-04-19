<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration;


use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\GatewayDomainResponseInterface;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;

class TestMapper implements ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response): GatewayDomainResponseInterface
    {
        return SimpleResponse::createFromEndpointResponse(
            $response
        );
    }
}

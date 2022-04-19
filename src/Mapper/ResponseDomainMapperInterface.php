<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Mapper;

use Profesia\ServiceLayer\Response\Domain\GatewayDomainResponseInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;

interface ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response): GatewayDomainResponseInterface;
}

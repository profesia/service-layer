<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Mapper;

use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;

interface ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response): DomainResponseInterface;
}

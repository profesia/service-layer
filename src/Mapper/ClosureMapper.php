<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Mapper;

use Closure;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;

/**
 * Mapper that uses a closure to transform endpoint responses to domain responses.
 * Useful for quick prototyping and testing without creating dedicated mapper classes.
 */
final class ClosureMapper implements ResponseDomainMapperInterface
{
    /**
     * @var Closure(EndpointResponseInterface): DomainResponseInterface
     */
    private Closure $mapper;

    /**
     * @param Closure(EndpointResponseInterface): DomainResponseInterface $mapper
     */
    public function __construct(Closure $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @inheritDoc
     */
    public function mapToDomain(EndpointResponseInterface $response): DomainResponseInterface
    {
        return ($this->mapper)($response);
    }
}

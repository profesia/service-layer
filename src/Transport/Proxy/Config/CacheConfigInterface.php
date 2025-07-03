<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Proxy\Config;

use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Psr\Http\Message\RequestInterface;

interface CacheConfigInterface
{
    public function getCacheKeyForRequest(RequestInterface $request): string;
    public function shouldBeResponseForRequestBeCached(RequestInterface $request, DomainResponseInterface $response): bool;
    public function getTtlForRequest(RequestInterface $request): ?int;
}
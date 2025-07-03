<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Proxy\Config;

use GuzzleHttp\Utils;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Psr\Http\Message\RequestInterface;

final class DefaultCacheConfig implements CacheConfigInterface
{
    public function getCacheKeyForRequest(RequestInterface $request): string
    {
        //@todo Double encoding in case of JSON body, should be addressed in the future
        $key = "{$request->getUri()}-{$request->getMethod()}-";
        $key .= Utils::jsonEncode((string)$request->getBody());

        return md5($key);
    }

    public function shouldBeResponseForRequestBeCached(RequestInterface $request, DomainResponseInterface $response): bool
    {
        return $response->isSuccessful();
    }

    public function getTtlForRequest(RequestInterface $request): ?int
    {
        return null;
    }
}
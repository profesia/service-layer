<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Proxy;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Proxy\Config\CacheConfigInterface;
use Profesia\ServiceLayer\Transport\Proxy\Config\DefaultCacheConfig;
use Psr\SimpleCache\CacheInterface;

final class GatewayCachingProxy implements GatewayInterface
{
    private CacheInterface       $cache;
    private GatewayInterface     $requestGateway;
    private CacheConfigInterface $cacheConfig;

    public function __construct(CacheInterface $cache, GatewayInterface $requestGateway, ?CacheConfigInterface $cacheConfig = null)
    {
        $this->cache          = $cache;
        $this->requestGateway = $requestGateway;
        $this->cacheConfig    = $cacheConfig ?: new DefaultCacheConfig();
    }

    public function viaAdapter(AdapterInterface $adapter): GatewayInterface
    {
        $this->requestGateway->viaAdapter($adapter);

        return $this;
    }

    public function useLogger(GatewayLoggerInterface $logger): GatewayInterface
    {
        $this->requestGateway->useLogger($logger);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigInterface $adapterOverrideConfigBuilder = null
    ): DomainResponseInterface
    {
        $psrRequest = $gatewayRequest->toPsrRequest();
        $key        = $this->cacheConfig->getCacheKeyForRequest($psrRequest);
        if ($this->cache->has($key)) {
            /** @phpstan-ignore-next-line */
            return unserialize((string)$this->cache->get($key));
        }

        $response = $this->requestGateway->sendRequest($gatewayRequest, $mapper, $adapterOverrideConfigBuilder);
        if ($this->cacheConfig->shouldBeResponseForRequestBeCached($psrRequest, $response) === true) {
            $this->cache->set(
                $key,
                serialize($response),
                $this->cacheConfig->getTtlForRequest($psrRequest)
            );
        }

        return $response;
    }
}

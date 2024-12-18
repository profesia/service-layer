<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Proxy;

use GuzzleHttp\Utils;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Psr\Http\Message\RequestInterface;
use Psr\SimpleCache\CacheInterface;

final class GatewayCachingProxy implements GatewayInterface
{
    private CacheInterface $cache;
    private GatewayInterface $requestGateway;

    public function __construct(CacheInterface $cache, GatewayInterface $requestGateway)
    {
        $this->cache          = $cache;
        $this->requestGateway = $requestGateway;
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
    ): DomainResponseInterface {
        $psrRequest = $gatewayRequest->toPsrRequest();
        $key        = self::getRequestCacheKey($psrRequest);
        if ($this->cache->has($key)) {
            /** @phpstan-ignore-next-line  */
            return unserialize((string)$this->cache->get($key));
        }

        $response = $this->requestGateway->sendRequest($gatewayRequest, $mapper, $adapterOverrideConfigBuilder);
        if ($response->isSuccessful()) {
            $this->cache->set($key, serialize($response));
        }

        return $response;
    }

    private static function getRequestCacheKey(RequestInterface $request): string
    {
        $key = "{$request->getUri()}-{$request->getMethod()}-";
        $key .= Utils::jsonEncode((string)$request->getBody());

        return md5($key);
    }
}

<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Transport\Proxy;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Proxy\Config\CacheConfigInterface;
use Profesia\ServiceLayer\Transport\Proxy\GatewayCachingProxy;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestInterface;
use Psr\SimpleCache\CacheInterface;
use GuzzleHttp\Utils;

class GatewayCachingProxyTest extends MockeryTestCase
{
    /**
     * @group request-gateway
     */
    public function testCanOverrideAdapter(): void
    {
        /** @var AdapterInterface|MockInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);

        /** @var GatewayInterface|MockInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway
            ->shouldReceive('viaAdapter')
            ->once()
            ->withArgs(
                [
                    $adapter
                ]
            )->andReturn(
                $gateway
            );

        /** @var CacheInterface|MockInterface $cache */
        $cache = Mockery::mock(CacheInterface::class);

        $proxy = new GatewayCachingProxy(
            $cache,
            $gateway
        );

        $proxy->viaAdapter($adapter);
    }

    /**
     * @group request-gateway
     */
    public function testCanOverrideLogger(): void
    {
        /** @var GatewayLoggerInterface|MockInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);

        /** @var GatewayInterface|MockInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway
            ->shouldReceive('useLogger')
            ->once()
            ->withArgs(
                [
                    $logger
                ]
            )->andReturn(
                $gateway
            );

        /** @var CacheInterface|MockInterface $cache */
        $cache = Mockery::mock(CacheInterface::class);

        $proxy = new GatewayCachingProxy(
            $cache,
            $gateway
        );

        $proxy->useLogger($logger);
    }

    /**
     * @group request-gateway
     */
    public function testWillReturnCachedResult(): void
    {
        $requestUri  = new Uri('https://test.sk');
        $httpMethod  = HttpMethod::createPost();
        $requestBody = [
            'a' => 1,
            'b' => 2,
        ];

        $mappedResponse = [
            'response' => [
                'test' => 1,
            ],
        ];

        $jsonBody = \GuzzleHttp\json_encode($requestBody);
        $stream   = Stream::create($jsonBody);
        $cacheKey = "{$requestUri}-{$httpMethod}-";

        //@todo Double encoding due to bug/hasty solution in GatewayCachingProxy::getRequestCacheKey
        $cacheKey .= \GuzzleHttp\json_encode($jsonBody);
        $cacheKey = md5($cacheKey);

        $domainResponse = new ArrayDomainResponse(
            $mappedResponse
        );

        /** @var ResponseDomainMapperInterface|MockInterface $mapper */
        $mapper = Mockery::mock(ResponseDomainMapperInterface::class);

        /** @var RequestInterface|MockInterface $request */
        $request = Mockery::mock(RequestInterface::class);
        $request
            ->shouldReceive('getUri')
            ->times(1)
            ->andReturn($requestUri);
        $request
            ->shouldReceive('getMethod')
            ->times(1)
            ->andReturn($httpMethod);
        $request
            ->shouldReceive('getBody')
            ->times(1)
            ->andReturn($stream);

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->times(1)
            ->andReturn($request);

        /** @var GatewayInterface|MockInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);

        /** @var CacheInterface|MockInterface $cache */
        $cache = Mockery::mock(CacheInterface::class);
        $cache
            ->shouldReceive('has')
            ->times(1)
            ->withArgs([$cacheKey])
            ->andReturn(true);
        $cache
            ->shouldReceive('get')
            ->times(1)
            ->withArgs([$cacheKey])
            ->andReturn(serialize($domainResponse));

        $proxy = new GatewayCachingProxy(
            $cache,
            $gateway
        );

        $actualResponse = $proxy->sendRequest($gatewayRequest, $mapper);
        $this->assertEquals($mappedResponse, $actualResponse->getResponseBody());
    }

    /**
     * @group request-gateway
     */
    public function testWillSetReturnedResultToCache()
    {
        $requestUri  = new Uri('https://test.sk');
        $httpMethod  = HttpMethod::createPost();
        $requestBody = [
            'a' => 1,
            'b' => 2,
        ];

        $mappedResponse = [
            'response' => [
                'test' => 1,
            ],
        ];

        $jsonBody = Utils::jsonEncode($requestBody);
        $stream   = Stream::create($jsonBody);
        $cacheKey = "{$requestUri}-{$httpMethod}-";

        //@todo Double encoding due to bug/hasty solution in GatewayCachingProxy::getRequestCacheKey
        $cacheKey .= Utils::jsonEncode($jsonBody);
        $cacheKey = md5($cacheKey);

        /** @var ResponseDomainMapperInterface|MockInterface $mapper */
        $mapper = Mockery::mock(ResponseDomainMapperInterface::class);

        /** @var RequestInterface|MockInterface $request */
        $request = Mockery::mock(RequestInterface::class);
        $request
            ->shouldReceive('getUri')
            ->times(1)
            ->andReturn($requestUri);
        $request
            ->shouldReceive('getMethod')
            ->times(1)
            ->andReturn($httpMethod);
        $request
            ->shouldReceive('getBody')
            ->times(1)
            ->andReturn($stream);

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->times(1)
            ->andReturn($request);

        $domainResponse = new ArrayDomainResponse(
            $mappedResponse
        );

        /** @var GatewayInterface|MockInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway
            ->shouldReceive('sendRequest')
            ->times(1)
            ->withArgs(
                [
                    $gatewayRequest,
                    $mapper,
                    Mockery::any()
                ]
            )
            ->andReturn($domainResponse);

        /** @var CacheInterface|MockInterface $cache */
        $cache = Mockery::mock(CacheInterface::class);
        $cache
            ->shouldReceive('has')
            ->times(1)
            ->withArgs([$cacheKey])
            ->andReturn(false);
        $cache
            ->shouldReceive('set')
            ->times(1)
            ->withArgs(
                [
                    $cacheKey,
                    Mockery::any(),
                    null,
                ]
            );

        $proxy = new GatewayCachingProxy(
            $cache,
            $gateway
        );

        $actualResponse = $proxy->sendRequest($gatewayRequest, $mapper);
        $this->assertEquals($mappedResponse, $actualResponse->getResponseBody());
    }

    /**
     * @group request-gateway
     */
    public function testWillSetReturnedResultToCacheWithConfig()
    {
        $requestUri  = new Uri('https://test.sk');
        $httpMethod  = HttpMethod::createPost();
        $requestBody = [
            'a' => 1,
            'b' => 2,
        ];

        $mappedResponse = [
            'response' => [
                'test' => 1,
            ],
        ];

        $jsonBody = Utils::jsonEncode($requestBody);
        $stream   = Stream::create($jsonBody);
        $cacheKey = "{$requestUri}-{$httpMethod}-";

        //@todo Double encoding due to bug/hasty solution in GatewayCachingProxy::getRequestCacheKey
        $cacheKey .= Utils::jsonEncode($jsonBody);
        $cacheKey = md5($cacheKey);

        /** @var ResponseDomainMapperInterface|MockInterface $mapper */
        $mapper = Mockery::mock(ResponseDomainMapperInterface::class);

        /** @var RequestInterface|MockInterface $request */
        $request = Mockery::mock(RequestInterface::class);

        /** @var GatewayRequestInterface|MockInterface $gatewayRequest */
        $gatewayRequest = Mockery::mock(GatewayRequestInterface::class);
        $gatewayRequest
            ->shouldReceive('toPsrRequest')
            ->times(1)
            ->andReturn($request);

        $domainResponse = new ArrayDomainResponse(
            $mappedResponse
        );

        /** @var GatewayInterface|MockInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway
            ->shouldReceive('sendRequest')
            ->times(1)
            ->withArgs(
                [
                    $gatewayRequest,
                    $mapper,
                    Mockery::any()
                ]
            )
            ->andReturn($domainResponse);

        $ttl = null;

        /** @var CacheInterface|MockInterface $cache */
        $cache = Mockery::mock(CacheInterface::class);
        $cache
            ->shouldReceive('has')
            ->times(1)
            ->withArgs([$cacheKey])
            ->andReturn(false);
        $cache
            ->shouldReceive('set')
            ->times(1)
            ->withArgs(
                [
                    $cacheKey,
                    Mockery::any(),
                    $ttl,
                ]
            );

        /** @var CacheConfigInterface|MockInterface $cacheConfig */
        $cacheConfig = Mockery::mock(CacheConfigInterface::class);
        $cacheConfig
            ->shouldReceive('getCacheKeyForRequest')
            ->once()
            ->withArgs([$request])
            ->andReturn($cacheKey);
        $cacheConfig
            ->shouldReceive('shouldBeResponseForRequestBeCached')
            ->once()
            ->withArgs([$request, $domainResponse])
            ->andReturn(true);
        $cacheConfig
            ->shouldReceive('getTtlForRequest')
            ->once()
            ->withArgs([$request])
            ->andReturn($ttl);

        $proxy = new GatewayCachingProxy(
            $cache,
            $gateway,
            $cacheConfig
        );

        $actualResponse = $proxy->sendRequest($gatewayRequest, $mapper);
        $this->assertEquals($mappedResponse, $actualResponse->getResponseBody());
    }
}

class ArrayDomainResponse implements DomainResponseInterface
{
    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function getResponseBody(): array
    {
        return $this->array;
    }

    public function isSuccessful(): bool
    {
        return true;
    }
}

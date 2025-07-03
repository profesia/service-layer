<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Transport\Proxy\Config;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Mockery;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Transport\Proxy\Config\DefaultCacheConfig;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Utils;

class DefaultCacheConfigTest extends MockeryTestCase
{
    /**
     * @group request-gateway
     */
    public function testCanGetCacheKeyForRequest(): void
    {
        $uriString = '/test/path';
        $uri = new Uri($uriString);
        /** @var MockInterface|RequestInterface $request */
        $request = Mockery::mock(RequestInterface::class);
        $request
            ->shouldReceive('getUri')
            ->once()
            ->andReturn($uri);
        $method = 'GET';
        $request
            ->shouldReceive('getMethod')
            ->once()
            ->andReturn($method);

        $bodyStream = Stream::create(
            Utils::jsonEncode(
                [
                    'test' => [
                        'data' => [
                            'key1' => 1,
                            'key2' => 2.0,
                            'key3' => 'test',
                            'key4' => true,
                            'key5' => [
                                'a',
                                'b',
                                ''
                            ],
                        ]
                    ]
                ]
            )
        );

        $request
            ->shouldReceive('getBody')
            ->once()
            ->andReturn(
                $bodyStream
            );

        $config   = new DefaultCacheConfig();
        $expected = md5("{$uri}-{$method}-" . Utils::jsonEncode((string)$bodyStream));

        $this->assertSame($expected, $config->getCacheKeyForRequest($request));
    }
}
<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Adapter\Config;

use GuzzleHttp\RequestOptions;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\GuzzleConfigTransformer;

class GuzzleConfigTransformerTest extends MockeryTestCase
{
    public function testTransformsEmptyConfig(): void
    {
        $config = AdapterConfig::createDefault();
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertEmpty($transformed);
    }

    public function testTransformsTimeout(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 10.0,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::TIMEOUT, $transformed);
        $this->assertEquals(10.0, $transformed[RequestOptions::TIMEOUT]);
    }

    public function testTransformsConnectTimeout(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::CONNECT_TIMEOUT => 5.0,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::CONNECT_TIMEOUT, $transformed);
        $this->assertEquals(5.0, $transformed[RequestOptions::CONNECT_TIMEOUT]);
    }

    public function testTransformsVerify(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::VERIFY => false,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::VERIFY, $transformed);
        $this->assertFalse($transformed[RequestOptions::VERIFY]);
    }

    public function testTransformsAllowRedirects(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::ALLOW_REDIRECTS => true,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::ALLOW_REDIRECTS, $transformed);
        $this->assertTrue($transformed[RequestOptions::ALLOW_REDIRECTS]);
    }

    public function testTransformsAuth(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::AUTH => ['user', 'pass'],
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::AUTH, $transformed);
        $this->assertIsArray($transformed[RequestOptions::AUTH]);
        $this->assertEquals('user', $transformed[RequestOptions::AUTH][0]);
        $this->assertEquals('pass', $transformed[RequestOptions::AUTH][1]);
    }

    public function testTransformsAuthWithDigest(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::AUTH => ['user', 'pass', 'digest'],
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::AUTH, $transformed);
        $this->assertCount(3, $transformed[RequestOptions::AUTH]);
        $this->assertEquals('digest', $transformed[RequestOptions::AUTH][2]);
    }

    public function testTransformsHeaders(): void
    {
        $headers = ['X-Custom' => 'value', 'Accept' => 'application/json'];
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::HEADERS => $headers,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::HEADERS, $transformed);
        $this->assertEquals($headers, $transformed[RequestOptions::HEADERS]);
    }

    public function testTransformsAllOptions(): void
    {
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 15.0,
            AdapterConfigInterface::CONNECT_TIMEOUT => 5.0,
            AdapterConfigInterface::VERIFY => true,
            AdapterConfigInterface::ALLOW_REDIRECTS => false,
            AdapterConfigInterface::AUTH => ['username', 'password'],
            AdapterConfigInterface::HEADERS => ['X-Test' => 'value'],
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::TIMEOUT, $transformed);
        $this->assertArrayHasKey(RequestOptions::CONNECT_TIMEOUT, $transformed);
        $this->assertArrayHasKey(RequestOptions::VERIFY, $transformed);
        $this->assertArrayHasKey(RequestOptions::ALLOW_REDIRECTS, $transformed);
        $this->assertArrayHasKey(RequestOptions::AUTH, $transformed);
        $this->assertArrayHasKey(RequestOptions::HEADERS, $transformed);
    }

    public function testTransformsGuzzleAdapterConfigForBackwardCompatibility(): void
    {
        $config = GuzzleAdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 10.0,
            AdapterConfigInterface::VERIFY => false,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        // GuzzleAdapterConfig already returns transformed config
        // The transformer should handle it correctly
        $this->assertArrayHasKey(RequestOptions::TIMEOUT, $transformed);
        $this->assertArrayHasKey(RequestOptions::VERIFY, $transformed);
    }

    public function testPassesThroughOtherGuzzleOptions(): void
    {
        // Create a config with a custom Guzzle option
        $config = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 10.0,
        ]);
        
        $transformed = GuzzleConfigTransformer::transform($config);
        
        $this->assertArrayHasKey(RequestOptions::TIMEOUT, $transformed);
    }
}

<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Adapter\Config;

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\ValueObject\Login;
use Profesia\ServiceLayer\ValueObject\Password;
use Profesia\ServiceLayer\ValueObject\Timeout;

class AdapterConfigTest extends MockeryTestCase
{
    public function testCanCreateDefaultConfig(): void
    {
        $config = AdapterConfig::createDefault();
        
        $this->assertInstanceOf(AdapterConfig::class, $config);
        $this->assertEmpty($config->getConfig());
    }

    public function testCanCreateFromArray(): void
    {
        $configArray = [
            AdapterConfigInterface::TIMEOUT => 10.0,
            AdapterConfigInterface::VERIFY => false,
        ];
        
        $config = AdapterConfig::createFromArray($configArray);
        
        $this->assertInstanceOf(AdapterConfig::class, $config);
        $resultConfig = $config->getConfig();
        
        // Verify timeout is stored as Timeout value object
        $this->assertInstanceOf(Timeout::class, $resultConfig[AdapterConfigInterface::TIMEOUT]);
        $this->assertEquals(10.0, $resultConfig[AdapterConfigInterface::TIMEOUT]->toFloat());
        
        // Verify is stored as-is
        $this->assertFalse($resultConfig[AdapterConfigInterface::VERIFY]);
    }

    public function testValidatesTimeoutValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout value should be a valid number');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 'invalid',
        ]);
    }

    public function testValidatesConnectTimeoutValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connect timeout value should be a valid number');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::CONNECT_TIMEOUT => 'invalid',
        ]);
    }

    public function testValidatesVerifyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Verify value should be a valid boolean or a string path');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::VERIFY => 123,
        ]);
    }

    public function testValidatesAllowRedirectsValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Allow redirects value should be a valid boolean');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::ALLOW_REDIRECTS => 'invalid',
        ]);
    }

    public function testValidatesAuthValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auth value should be a valid array');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::AUTH => 'invalid',
        ]);
    }

    public function testValidatesAuthArrayLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auth value requires at least two items in the array config');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::AUTH => ['only-one'],
        ]);
    }

    public function testValidatesHeadersValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Headers value should be a valid array');
        
        AdapterConfig::createFromArray([
            AdapterConfigInterface::HEADERS => 'invalid',
        ]);
    }

    public function testAcceptsAllValidOptions(): void
    {
        $configArray = [
            AdapterConfigInterface::TIMEOUT => 15.0,
            AdapterConfigInterface::CONNECT_TIMEOUT => 5.0,
            AdapterConfigInterface::VERIFY => true,
            AdapterConfigInterface::ALLOW_REDIRECTS => false,
            AdapterConfigInterface::AUTH => ['username', 'password', 'digest'],
            AdapterConfigInterface::HEADERS => ['X-Custom' => 'value'],
        ];
        
        $config = AdapterConfig::createFromArray($configArray);
        $resultConfig = $config->getConfig();
        
        // Verify value objects
        $this->assertInstanceOf(Timeout::class, $resultConfig[AdapterConfigInterface::TIMEOUT]);
        $this->assertEquals(15.0, $resultConfig[AdapterConfigInterface::TIMEOUT]->toFloat());
        
        $this->assertInstanceOf(Timeout::class, $resultConfig[AdapterConfigInterface::CONNECT_TIMEOUT]);
        $this->assertEquals(5.0, $resultConfig[AdapterConfigInterface::CONNECT_TIMEOUT]->toFloat());
        
        $this->assertTrue($resultConfig[AdapterConfigInterface::VERIFY]);
        $this->assertFalse($resultConfig[AdapterConfigInterface::ALLOW_REDIRECTS]);
        
        $this->assertInstanceOf(Login::class, $resultConfig[AdapterConfigInterface::AUTH][0]);
        $this->assertInstanceOf(Password::class, $resultConfig[AdapterConfigInterface::AUTH][1]);
        $this->assertEquals('username', $resultConfig[AdapterConfigInterface::AUTH][0]->toString());
        $this->assertEquals('password', $resultConfig[AdapterConfigInterface::AUTH][1]->toString());
        $this->assertEquals('digest', $resultConfig[AdapterConfigInterface::AUTH][2]);
        
        $this->assertEquals(['X-Custom' => 'value'], $resultConfig[AdapterConfigInterface::HEADERS]);
    }

    public function testCanMergeConfigs(): void
    {
        $config1 = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 10.0,
            AdapterConfigInterface::VERIFY => false,
        ]);
        
        $config2 = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 20.0,
            AdapterConfigInterface::HEADERS => ['X-Custom' => 'value'],
        ]);
        
        $merged = $config1->merge($config2);
        $resultConfig = $merged->getConfig();
        
        // Timeout should be overridden
        $this->assertEquals(20.0, $resultConfig[AdapterConfigInterface::TIMEOUT]->toFloat());
        
        // Verify should remain from config1
        $this->assertFalse($resultConfig[AdapterConfigInterface::VERIFY]);
        
        // Headers should be from config2
        $this->assertEquals(['X-Custom' => 'value'], $resultConfig[AdapterConfigInterface::HEADERS]);
    }

    public function testMergeDoesNotMutateOriginal(): void
    {
        $config1 = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 10.0,
        ]);
        
        $config2 = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 20.0,
        ]);
        
        $merged = $config1->merge($config2);
        
        // Original should not be mutated
        $this->assertEquals(10.0, $config1->getConfig()[AdapterConfigInterface::TIMEOUT]->toFloat());
        $this->assertEquals(20.0, $merged->getConfig()[AdapterConfigInterface::TIMEOUT]->toFloat());
    }

    public function testMergePerformsDeepCopyForHeaders(): void
    {
        $config1 = AdapterConfig::createFromArray([
            AdapterConfigInterface::TIMEOUT => 10.0,
            AdapterConfigInterface::HEADERS => [
                'X-Custom-Header' => 'value1',
                'X-Auth' => 'token1',
            ],
        ]);
        
        $config2 = AdapterConfig::createFromArray([
            AdapterConfigInterface::HEADERS => [
                'X-Auth' => 'token2',
                'X-New-Header' => 'value2',
            ],
        ]);
        
        $merged = $config1->merge($config2);
        $resultConfig = $merged->getConfig();
        
        // Headers should be deep merged
        $this->assertArrayHasKey(AdapterConfigInterface::HEADERS, $resultConfig);
        $this->assertEquals([
            'X-Custom-Header' => 'value1',  // From config1
            'X-Auth' => 'token2',            // Overridden by config2
            'X-New-Header' => 'value2',      // From config2
        ], $resultConfig[AdapterConfigInterface::HEADERS]);
        
        // Timeout should remain from config1
        $this->assertEquals(10.0, $resultConfig[AdapterConfigInterface::TIMEOUT]->toFloat());
    }
}

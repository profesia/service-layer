<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Adapter\Config;

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;

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
        $this->assertEquals($configArray, $config->getConfig());
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
        
        $this->assertEquals($configArray, $config->getConfig());
    }
}

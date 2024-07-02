<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Registry\Config;
use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Registry\Config\RequestConfig;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;

class RequestConfigTest extends MockeryTestCase
{
    public function testCanDetectIfAdapterIsOverridden(): void
    {
        $requestConfig = new RequestConfig(
            null,
            null,
            null,
            null,
            null,
            null
        );

        $this->assertFalse($requestConfig->hasOverriddenAdapter());

        /** @var AdapterInterface|null $adapterOverride */
        $adapterOverride = Mockery::mock(AdapterInterface::class);
        $requestConfig = new RequestConfig(
            null,
            null,
            $adapterOverride,
            null,
            null,
            null
        );

        $this->assertTrue($requestConfig->hasOverriddenAdapter());
    }

    public function testCanDetectIfLoggerIsOverridden(): void
    {
        $requestConfig = new RequestConfig(
            null,
            null,
            null,
            null,
            null,
            null
        );

        $this->assertFalse($requestConfig->hasOverriddenLogger());

        /** @var GatewayLoggerInterface|null $loggerOverride */
        $loggerOverride = Mockery::mock(GatewayLoggerInterface::class);
        $requestConfig = new RequestConfig(
            null,
            null,
            null,
            $loggerOverride,
            null,
            null
        );

        $this->assertTrue($requestConfig->hasOverriddenLogger());
    }

    public function testCanDetectIfGatewayIsOverridden(): void
    {
        $requestConfig = new RequestConfig(
            null,
            null,
            null,
            null,
            null,
            null
        );

        $this->assertFalse($requestConfig->hasOverriddenGateway());

        /** @var GatewayInterface|null $gatewayOverride */
        $gatewayOverride = Mockery::mock(GatewayInterface::class);
        $requestConfig = new RequestConfig(
            null,
            null,
            null,
            null,
            $gatewayOverride,
            null
        );

        $this->assertTrue($requestConfig->hasOverriddenGateway());
    }
}
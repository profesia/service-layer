<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration\Request\Factory;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;
use Mockery\MockInterface;
use Profesia\ServiceLayer\Test\Integration\TestGatewayRequestFactory;
use Profesia\ServiceLayer\Test\Integration\TestRequest1;
use Psr\Http\Message\RequestFactoryInterface;

class ConcreteRequestGatewayFactoryTest extends MockeryTestCase
{
    public function test(): void
    {
        /** @var MockInterface|RequestFactoryInterface $psrFactory */
        $psrFactory = Mockery::mock(RequestFactoryInterface::class);

        $requestFactory = new TestGatewayRequestFactory(
            $psrFactory
        );

        $expectedGatewayRequest = new TestRequest1(
            $psrFactory
        );

        $actualGatewayRequest = $requestFactory->create();
        $this->assertEquals(
            $expectedGatewayRequest,
            $actualGatewayRequest
        );
    }
}

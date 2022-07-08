<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Unit\Registry;

use Mockery\MockInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Profesia\ServiceLayer\Adapter\Config\AbstractAdapterConfig;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Registry\GatewayUseCase;
use Profesia\ServiceLayer\Registry\GatewayUseCaseRegistry;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\Test\Integration\TestRequest1;
use Profesia\ServiceLayer\Test\Integration\TestRequest3;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Profesia\ServiceLayer\Registry\Exception\BadConfigException;

class GatewayUseCaseRegistryTest extends MockeryTestCase
{
    public function provideDataForHandlingRequest(): array
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);

        $psrFactory = new Psr17Factory();

        $request1 = new TestRequest1(
            $psrFactory
        );

        $request2 = new TestRequest1(
            $psrFactory
        );

        $request3 = new TestRequest3(
            $psrFactory
        );

        return [
            [
                [
                    'defaultGateway' => $gateway,
                    'requests'       => [
                        'request1' => [
                            'request' => $request1,
                        ],
                        'request2' => [
                            'request' => $request2,
                        ],
                        'request3' => [
                            'request' => $request3,
                        ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $gateway,
                    'requests'       => [
                        'request1' => [
                            'request' => $request1,
                            'mapper'  => Mockery::mock(ResponseDomainMapperInterface::class),
                        ],
                        'request2' => [
                            'request' => $request2,
                            'mapper'  => Mockery::mock(ResponseDomainMapperInterface::class),
                        ],
                        'request3' => [
                            'request' => $request3,
                            'mapper'  => Mockery::mock(ResponseDomainMapperInterface::class),
                        ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $gateway,
                    'requests'       => [
                        'request1' => [
                            'request'        => $request1,
                            'mapper'         => Mockery::mock(ResponseDomainMapperInterface::class),
                            'configOverride' => Mockery::mock(AbstractAdapterConfig::class),
                        ],
                        'request2' => [
                            'request'        => $request2,
                            'mapper'         => Mockery::mock(ResponseDomainMapperInterface::class),
                            'configOverride' => Mockery::mock(AbstractAdapterConfig::class),
                        ],
                        'request3' => [
                            'request'        => $request3,
                            'mapper'         => Mockery::mock(ResponseDomainMapperInterface::class),
                            'configOverride' => Mockery::mock(AbstractAdapterConfig::class),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testWillThrowExceptionOnMissingDefaultGateway(): void
    {
        $this->expectExceptionObject(
            new BadConfigException('Required key: [defaultGateway] is not set in config')
        );
        GatewayUseCaseRegistry::createFromArrayConfig(
            []
        );
    }

    public function testWillThrowExceptionOnMissingRequestsConfig(): void
    {
        $this->expectExceptionObject(
            new BadConfigException('Required key: [requests] is not set in config')
        );
        GatewayUseCaseRegistry::createFromArrayConfig(
            [
                'defaultGateway' => Mockery::mock(GatewayInterface::class),
            ]
        );
    }

    public function testWillThrowExceptionOnEmptyRequestsConfig(): void
    {
        $this->expectExceptionObject(
            new BadConfigException('Required key: [requests] is empty')
        );
        GatewayUseCaseRegistry::createFromArrayConfig(
            [
                'defaultGateway' => Mockery::mock(GatewayInterface::class),
                'requests'       => [],
            ]
        );
    }

    /**
     * @param array $config
     *
     * @return void
     * @throws BadConfigException
     *
     * @dataProvider provideDataForHandlingRequest
     */
    public function testCanHandleRequest(array $config): void
    {
        $registry = GatewayUseCaseRegistry::createFromArrayConfig(
            $config
        );

        /** @var MockInterface $gateway */
        $gateway = $config['defaultGateway'];

        foreach ($config['requests'] as $requestName => $requestConfig) {
            $psrRequest             = $requestConfig['request']->toPsrRequest();
            $expectedDomainResponse = SimpleResponse::createFromStatusCodeAndStream(
                StatusCode::createFromInteger(200),
                $psrRequest->getBody()
            );

            $gateway
                ->shouldReceive('sendRequest')
                ->once()
                ->withArgs(
                    [
                        $requestConfig['request'],
                        $requestConfig['mapper'] ?? null,
                        $requestConfig['configOverride'] ?? null,
                    ]
                )->andReturn(
                    $expectedDomainResponse
                );

            $actualResponse = $registry->processUseCase($requestName);


            $this->assertEquals($expectedDomainResponse->isSuccessful(), $actualResponse->isSuccessful());

            $expectedDomainResponse->getResponseBody()->rewind();
            $expectedResponseBody = $expectedDomainResponse->getResponseBody()->getContents();
            $actualResponseBody   = $actualResponse->getResponseBody();
            $actualResponseBody->rewind();
            $this->assertEquals($expectedResponseBody, $actualResponseBody->getContents());
        }
    }

    /**
     * @param array $config
     *
     * @return void
     * @throws BadConfigException
     *
     * @dataProvider provideDataForHandlingRequest
     */
    public function testCanGetConfiguredUseCase(array $config): void
    {
        $registry = GatewayUseCaseRegistry::createFromArrayConfig(
            $config
        );

        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = $config['defaultGateway'];

        foreach ($config['requests'] as $requestName => $requestConfig) {
            $actualUseCase   = $registry->getConfiguredGatewayUseCase($requestName);
            $expectedUseCase = new GatewayUseCase(
                $gateway,
                $requestConfig['request'],
                $requestConfig['mapper'] ?? null,
                $requestConfig['configOverride'] ?? null
            );

            $this->assertEquals($expectedUseCase, $actualUseCase);
        }
    }
}

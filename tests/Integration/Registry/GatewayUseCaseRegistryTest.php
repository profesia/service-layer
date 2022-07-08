<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Integration\Registry;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use Profesia\ServiceLayer\Registry\Exception\BadConfigException;
use Profesia\ServiceLayer\Registry\Exception\RequestNotRegisteredException;
use Profesia\ServiceLayer\Registry\GatewayUseCase;
use Profesia\ServiceLayer\Registry\GatewayUseCaseRegistry;
use Profesia\ServiceLayer\Test\Integration\NullGateway;
use Profesia\ServiceLayer\Test\Integration\TestingAdapter;
use Profesia\ServiceLayer\Test\Integration\TestMapper;
use Profesia\ServiceLayer\Test\Integration\TestRequest1;
use Profesia\ServiceLayer\Test\Integration\TestRequest2;
use Profesia\ServiceLayer\Test\Integration\TestRequest3;
use Profesia\ServiceLayer\Transport\Logging\DefaultRequestGatewayLogger;
use Profesia\ServiceLayer\Transport\Gateway;
use Psr\Log\NullLogger;

class GatewayUseCaseRegistryTest extends MockeryTestCase
{
    public function provideInputConfigForCreatingRegistry(): array
    {
        $adapter       = new TestingAdapter();
        $gatewayLogger = new DefaultRequestGatewayLogger(
            new NullLogger()
        );

        $defaultGateway = new Gateway(
            $adapter,
            $gatewayLogger
        );

        $requestFactory = new Psr17Factory();
        $testMapper     = new TestMapper();

        $loggerOverride = new DefaultRequestGatewayLogger(
            new NullLogger
        );

        return [
            [
                [
                    'defaultGateway' => $defaultGateway,
                    'requests'       => [
                        'request1' =>
                            [
                                'request' => new TestRequest1(
                                    $requestFactory
                                ),
                            ],
                        'request2' => [
                            'request' => new TestRequest2(
                                $requestFactory
                            ),
                        ],
                        'request3' => [
                            'request' => new TestRequest3(
                                $requestFactory
                            ),
                        ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $defaultGateway,
                    'requests'       => [
                        'request1' =>
                            [
                                'request'        => new TestRequest1(
                                    $requestFactory
                                ),
                                'configOverride' => GuzzleAdapterConfig::createFromArray(
                                    [

                                    ]
                                ),
                            ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $defaultGateway,
                    'requests'       => [
                        'request1' =>
                            [
                                'request' => new TestRequest1(
                                    $requestFactory
                                ),
                                'mapper'  => $testMapper,
                            ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $defaultGateway,
                    'requests'       => [
                        'request1' =>
                            [
                                'request'        => new TestRequest1(
                                    $requestFactory
                                ),
                                'loggerOverride' => $loggerOverride,
                            ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $defaultGateway,
                    'requests'       => [
                        'request1' =>
                            [
                                'request'         => new TestRequest1(
                                    $requestFactory
                                ),
                                'adapterOverride' => new TestingAdapter(),
                            ],
                    ],
                ],
            ],
            [
                [
                    'defaultGateway' => $defaultGateway,
                    'requests'       => [
                        'request1' =>
                            [
                                'request'         => new TestRequest1(
                                    $requestFactory
                                ),
                                'gatewayOverride' => new NullGateway(),
                            ],
                    ],
                ],
            ],
        ];
    }

    public function testCanHandleNonRegisteredRequest(): void
    {
        $adapter       = new TestingAdapter();
        $gatewayLogger = new DefaultRequestGatewayLogger(
            new NullLogger()
        );

        $defaultGateway = new Gateway(
            $adapter,
            $gatewayLogger
        );

        $gateway = GatewayUseCaseRegistry::createFromArrayConfig(
            [
                'defaultGateway' => $defaultGateway,
                'requests'       => [
                    'AnotherRequest' => [],
                ],
            ]
        );

        $requestName = 'testRequest';
        $this->expectExceptionObject(
            new RequestNotRegisteredException("Request with name: [{$requestName}] is not registered")
        );
        $gateway->processUseCase($requestName);
    }

    /**
     * @param array $config
     *
     * @return void
     * @throws BadConfigException
     * @throws RequestNotRegisteredException
     * @dataProvider provideInputConfigForCreatingRegistry
     */
    public function testCanProcessUseCase(array $config): void
    {
        $gateway = GatewayUseCaseRegistry::createFromArrayConfig(
            $config
        );

        if (!isset($config['requests'])) {
            $this->assertTrue(true);

            return;
        }

        foreach ($config['requests'] as $requestName => $requestConfig) {
            $response = $gateway->processUseCase($requestName);
            $this->assertTrue(
                $response->isSuccessful()
            );


            $psrRequest   = $requestConfig['request']->toPsrRequest();
            $expectedBody = $psrRequest->getBody();
            $expectedBody->rewind();
            $actualBody = $response->getResponseBody();
            $actualBody->rewind();
            $this->assertEquals(
                $expectedBody->getContents(),
                $actualBody->getContents()
            );
        }
    }

    /**
     * @param array $config
     *
     * @return void
     * @throws BadConfigException
     * @throws RequestNotRegisteredException
     * @dataProvider provideInputConfigForCreatingRegistry
     */
    public function testCanGetConfiguredUseCase(array $config): void
    {
        $registry = GatewayUseCaseRegistry::createFromArrayConfig(
            $config
        );

        $defaultGateway = $config['defaultGateway'];
        if (!isset($config['requests'])) {
            $this->assertTrue(true);

            return;
        }

        foreach ($config['requests'] as $requestName => $requestConfig) {
            $expectedUseCase = new GatewayUseCase(
                $defaultGateway,
                $requestConfig['request'],
                $requestConfig['mapper'] ?? null,
                $requestConfig['configOverride'] ?? null
            );

            if (array_key_exists('loggerOverride', $requestConfig)) {
                $expectedUseCase->useLogger($requestConfig['loggerOverride']);
            }

            if (array_key_exists('adapterOverride', $requestConfig)) {
                $expectedUseCase->viaAdapter($requestConfig['adapterOverride']);
            }

            if (array_key_exists('gatewayOverride', $requestConfig)) {
                $expectedUseCase->throughGatewayOverride($requestConfig['gatewayOverride']);
            }

            $response = $registry->getConfiguredGatewayUseCase($requestName);
            $this->assertEquals(
                $expectedUseCase,
                $response
            );
        }
    }

    public function testWillThrowAnExceptionOnGettingOfNonRegisteredUseCase()
    {
        $adapter       = new TestingAdapter();
        $gatewayLogger = new DefaultRequestGatewayLogger(
            new NullLogger()
        );

        $defaultGateway = new Gateway(
            $adapter,
            $gatewayLogger
        );

        $registry = GatewayUseCaseRegistry::createFromArrayConfig(
            [
                'defaultGateway' => $defaultGateway,
                'requests'       => [
                    'AnotherRequest' => [],
                ],
            ]
        );

        $requestName = 'Testing';
        $this->expectExceptionObject(
            new RequestNotRegisteredException("Request with name: [{$requestName}] is not registered")
        );
        $registry->getConfiguredGatewayUseCase($requestName);
    }
}

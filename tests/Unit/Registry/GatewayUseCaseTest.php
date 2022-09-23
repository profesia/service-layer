<?php

declare(strict_types=1);


namespace Profesia\ServiceLayer\Test\Unit\Registry;


use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Adapter\Config\AbstractAdapterConfig;
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Registry\GatewayUseCase;
use Mockery\MockInterface;
use Mockery;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\Registry\Exception\BadStateException;

class GatewayUseCaseTest extends MockeryTestCase
{
    public function testWillThrowAnExceptionOnMissingRequest(): void
    {
        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway
        );

        $this->expectExceptionObject(
            new BadStateException(
                'Request to send was not set. Before invoking `performRequest` you have to set request first'
            )
        );
        $gatewayUseCase->performRequest();
    }

    public function testWillUseDefaultGateway(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    null,
                    null,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request
        );

        $gatewayUseCase->performRequest();
    }

    public function testWillUseSetRequest(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    null,
                    null,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway
        );


        $gatewayUseCase->setRequestToSend(
            $request
        );
        $gatewayUseCase->performRequest();
    }

    public function testWillOverrideRequestSetDuringCreation(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request1 */
        $request1 = Mockery::mock(GatewayRequestInterface::class);

        /** @var GatewayRequestInterface|MockInterface $request2 */
        $request2 = Mockery::mock(GatewayRequestInterface::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request2,
                    null,
                    null,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request1
        );


        $gatewayUseCase->setRequestToSend(
            $request2
        );
        $gatewayUseCase->performRequest();
    }

    public function testWillUseGatewayOverride(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldNotReceive('sendRequest');

        /** @var GatewayInterface|MockInterface $gatewayOverride */
        $gatewayOverride = Mockery::mock(GatewayInterface::class);
        $gatewayOverride
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    null,
                    null,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request
        );
        $gatewayUseCase->throughGatewayOverride(
            $gatewayOverride
        );

        $gatewayUseCase->performRequest();
    }

    public function testWillUseAdapterOverride(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var MockInterface|AdapterInterface $adapter */
        $adapter = Mockery::mock(AdapterInterface::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldNotReceive('viaAdapter')
            ->once()
            ->withArgs(
                [
                    $adapter,
                ]
            )->andReturn(
                $defaultGateway
            );

        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    null,
                    null,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request
        );
        $gatewayUseCase->viaAdapter($adapter);

        $gatewayUseCase->performRequest();
    }

    public function testWillUseLoggerOverride(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var MockInterface|GatewayLoggerInterface $logger */
        $logger = Mockery::mock(GatewayLoggerInterface::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldNotReceive('useLogger')
            ->once()
            ->withArgs(
                [
                    $logger,
                ]
            )->andReturn(
                $defaultGateway
            );

        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    null,
                    null,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request
        );
        $gatewayUseCase->useLogger($logger);

        $gatewayUseCase->performRequest();
    }

    public function testWillUseMapperOverride(): void
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var ResponseDomainMapperInterface|MockInterface $defaultMapper */
        $defaultMapper = Mockery::mock(ResponseDomainMapperInterface::class);

        /** @var ResponseDomainMapperInterface|MockInterface $mapperOverride */
        $mapperOverride = Mockery::mock(ResponseDomainMapperInterface::class);

        /** @var AbstractAdapterConfig|MockInterface $configOverride */
        $configOverride = Mockery::mock(AbstractAdapterConfig::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    $mapperOverride,
                    $configOverride,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request,
            $defaultMapper,
            $configOverride
        );

        $gatewayUseCase->withMapper($mapperOverride);

        $gatewayUseCase->performRequest();
    }

    public function testWillUseOptionalParams()
    {
        /** @var GatewayRequestInterface|MockInterface $request */
        $request = Mockery::mock(GatewayRequestInterface::class);

        /** @var ResponseDomainMapperInterface|MockInterface $mapper */
        $mapper = Mockery::mock(ResponseDomainMapperInterface::class);

        /** @var AbstractAdapterConfig|MockInterface $configOverride */
        $configOverride = Mockery::mock(AbstractAdapterConfig::class);

        /** @var GatewayInterface|MockInterface $defaultGateway */
        $defaultGateway = Mockery::mock(GatewayInterface::class);
        $defaultGateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(
                [
                    $request,
                    $mapper,
                    $configOverride,
                ]
            );

        $gatewayUseCase = new GatewayUseCase(
            $defaultGateway,
            $request,
            $mapper,
            $configOverride
        );

        $gatewayUseCase->performRequest();
    }
}

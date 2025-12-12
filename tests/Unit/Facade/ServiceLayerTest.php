<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Facade;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Facade\ServiceLayer;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

class ServiceLayerTest extends MockeryTestCase
{
    public function testCanExecuteRequestWithUriAndMethod(): void
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        
        /** @var MockInterface|DomainResponseInterface $expectedResponse */
        $expectedResponse = Mockery::mock(DomainResponseInterface::class);
        
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (GatewayRequestInterface $request) {
                $psrRequest = $request->toPsrRequest();
                
                if ($psrRequest->getMethod() !== 'POST') {
                    return false;
                }
                
                if ((string)$psrRequest->getUri() !== 'https://example.com/api/test') {
                    return false;
                }
                
                if ((string)$psrRequest->getBody() !== 'test body') {
                    return false;
                }
                
                return true;
            })
            ->andReturn($expectedResponse);

        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $uri = new Uri('https://example.com/api/test');
        $body = Stream::create('test body');
        $response = $facade->sendRequest($uri, HttpMethod::createPost(), $body);
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testCanExecuteRequestWithUriObject(): void
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        
        /** @var MockInterface|DomainResponseInterface $expectedResponse */
        $expectedResponse = Mockery::mock(DomainResponseInterface::class);
        
        $uri = new Uri('https://example.com/api/endpoint');
        
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (GatewayRequestInterface $request) use ($uri) {
                $psrRequest = $request->toPsrRequest();
                
                if ($psrRequest->getMethod() !== 'GET') {
                    return false;
                }
                
                if ($psrRequest->getUri() !== $uri) {
                    return false;
                }
                
                return true;
            })
            ->andReturn($expectedResponse);

        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $response = $facade->sendRequest($uri, HttpMethod::createGet(), null);
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testCanExecuteRequestWithoutBody(): void
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        
        /** @var MockInterface|DomainResponseInterface $expectedResponse */
        $expectedResponse = Mockery::mock(DomainResponseInterface::class);
        
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (GatewayRequestInterface $request) {
                $psrRequest = $request->toPsrRequest();
                
                if ($psrRequest->getMethod() !== 'DELETE') {
                    return false;
                }
                
                if ($psrRequest->getBody()->getSize() !== 0) {
                    return false;
                }
                
                return true;
            })
            ->andReturn($expectedResponse);

        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $uri = new Uri('https://example.com/api/resource/123');
        $response = $facade->sendRequest($uri, HttpMethod::createDelete());
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testCanExecuteRequestWithClientOptions(): void
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        
        /** @var MockInterface|DomainResponseInterface $expectedResponse */
        $expectedResponse = Mockery::mock(DomainResponseInterface::class);
        
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (
                GatewayRequestInterface $request,
                $mapper,
                $adapterConfig
            ) {
                $psrRequest = $request->toPsrRequest();
                
                if ($psrRequest->getMethod() !== 'GET') {
                    return false;
                }
                
                // Verify adapter config is passed
                if ($adapterConfig === null) {
                    return false;
                }
                
                return true;
            })
            ->andReturn($expectedResponse);

        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $uri = new Uri('https://example.com/api/test');
        $clientOptions = [
            'timeout' => 10.0,
            'verify' => false
        ];
        $response = $facade->sendRequest($uri, HttpMethod::createGet(), null, $clientOptions);
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testCanUseBuilderPatternWithMapper(): void
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        
        /** @var MockInterface|DomainResponseInterface $expectedResponse */
        $expectedResponse = Mockery::mock(DomainResponseInterface::class);
        
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (
                GatewayRequestInterface $request,
                $mapper,
                $adapterConfig
            ) {
                // Verify mapper is passed
                if ($mapper === null) {
                    return false;
                }
                
                return true;
            })
            ->andReturn($expectedResponse);

        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $uri = new Uri('https://example.com/api/test');
        $response = $facade
            ->withMapperClosure(function ($endpointResponse) use ($expectedResponse) {
                return $expectedResponse;
            })
            ->sendRequest($uri, HttpMethod::createGet());
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testBuilderPatternResetsStateAfterRequest(): void
    {
        /** @var MockInterface|GatewayInterface $gateway */
        $gateway = Mockery::mock(GatewayInterface::class);
        
        // First request with mapper - mapper should be present
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (
                GatewayRequestInterface $request,
                $mapper,
                $adapterConfig
            ) {
                // First call should have mapper
                return $mapper !== null;
            })
            ->andReturn(Mockery::mock(DomainResponseInterface::class));
        
        // Second request should not have mapper (state reset) - mapper should be null
        $gateway
            ->shouldReceive('sendRequest')
            ->once()
            ->withArgs(function (
                GatewayRequestInterface $request,
                $mapper,
                AdapterConfigInterface $adapterConfig
            ) {
                // Mapper should be null after reset
                return $mapper === null && $adapterConfig->getConfig() === [];
            })
            ->andReturn(Mockery::mock(DomainResponseInterface::class));

        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        // First request with mapper
        $uri = new Uri('https://example.com/api/test');
        $facade
            ->withMapperClosure(function ($response) {
                return Mockery::mock(DomainResponseInterface::class);
            })
            ->sendRequest($uri, HttpMethod::createGet());
        
        // Second request should not have mapper (state reset)
        $facade->sendRequest($uri, HttpMethod::createGet());
    }
}

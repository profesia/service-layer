<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Transport;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Transport\GatewayInterface;
use Profesia\ServiceLayer\Transport\SimpleFacade;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

class SimpleFacadeTest extends MockeryTestCase
{
    public function testCanExecuteRequestWithStringUri(): void
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

        $facade = new SimpleFacade($gateway, new Psr17Factory());
        
        $body = Stream::create('test body');
        $response = $facade->executeRequest('https://example.com/api/test', 'POST', $body);
        
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

        $facade = new SimpleFacade($gateway, new Psr17Factory());
        
        $response = $facade->executeRequest($uri, 'GET', null);
        
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

        $facade = new SimpleFacade($gateway, new Psr17Factory());
        
        $response = $facade->executeRequest('https://example.com/api/resource/123', 'DELETE');
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testCanExecuteRequestWithLowercaseMethod(): void
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
                return $psrRequest->getMethod() === 'PUT';
            })
            ->andReturn($expectedResponse);

        $facade = new SimpleFacade($gateway, new Psr17Factory());
        
        $body = Stream::create('{"key": "value"}');
        $response = $facade->executeRequest('https://example.com/api/update', 'put', $body);
        
        $this->assertSame($expectedResponse, $response);
    }

    public function testCanCreateWithDefaultDependencies(): void
    {
        $facade = new SimpleFacade();
        
        $this->assertInstanceOf(SimpleFacade::class, $facade);
    }
}

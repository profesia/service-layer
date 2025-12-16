<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Mapper;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Profesia\ServiceLayer\Mapper\ClosureMapper;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;

class ClosureMapperTest extends MockeryTestCase
{
    public function testCanMapUsingClosure(): void
    {
        /** @var MockInterface|EndpointResponseInterface $endpointResponse */
        $endpointResponse = Mockery::mock(EndpointResponseInterface::class);
        
        /** @var MockInterface|DomainResponseInterface $expectedDomainResponse */
        $expectedDomainResponse = Mockery::mock(DomainResponseInterface::class);
        
        $mapper = new ClosureMapper(function (EndpointResponseInterface $response) use ($expectedDomainResponse, $endpointResponse) {
            // Verify we received the correct endpoint response
            $this->assertSame($endpointResponse, $response);
            return $expectedDomainResponse;
        });
        
        $result = $mapper->mapToDomain($endpointResponse);
        
        $this->assertSame($expectedDomainResponse, $result);
    }

    public function testClosureReceivesEndpointResponse(): void
    {
        /** @var MockInterface|EndpointResponseInterface $endpointResponse */
        $endpointResponse = Mockery::mock(EndpointResponseInterface::class);
        
        $closureCalled = false;
        
        $mapper = new ClosureMapper(function (EndpointResponseInterface $response) use (&$closureCalled, $endpointResponse) {
            $closureCalled = true;
            $this->assertSame($endpointResponse, $response);
            
            /** @var MockInterface|DomainResponseInterface $mockResponse */
            $mockResponse = Mockery::mock(DomainResponseInterface::class);
            return $mockResponse;
        });
        
        $mapper->mapToDomain($endpointResponse);
        
        $this->assertTrue($closureCalled);
    }
}

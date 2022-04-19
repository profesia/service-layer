<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Response\Connection;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Psr\Http\Message\ResponseInterface;

class EndpointResponseTest extends MockeryTestCase
{
    public function testCanDetectSuccess()
    {
        $statusCodes = [200, 201, 400, 404, 500];
        foreach ($statusCodes as $code) {
            $isSuccessful = ($code < 300);
            /** @var ResponseInterface|MockInterface $psrResponse */
            $psrResponse = Mockery::mock(ResponseInterface::class);

            $psrResponse
                ->shouldNotReceive('getStatusCode')
                ->once()
                ->andReturn(
                    $code
                );

            $psrResponse
                ->shouldReceive('getBody')
                ->once()
                ->andReturn(Stream::create('body'));

            $psrResponse
                ->shouldReceive('getHeaders')
                ->once()
                ->andReturn(
                    [
                        'Content-type' => 'application/json'
                    ]
                );

            $response = EndpointResponse::createFromPsrResponse($psrResponse);
            $this->assertEquals($isSuccessful, $response->isSuccessful());
        }
    }
}
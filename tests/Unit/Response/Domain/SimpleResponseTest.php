<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Response\Domain;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\ValueObject\StatusCode;

class SimpleResponseTest extends MockeryTestCase
{
    public function testCanDetectSuccess()
    {
        $statusCodes       = StatusCode::getListOfStatusCodes();
        $successStatusCode = StatusCode::getListOfSuccessStatusCodes();

        foreach ($statusCodes as $code) {
            $statusCode = StatusCode::createFromInteger($code);

            /** @var MockInterface|EndpointResponseInterface $endpointResponse */
            $endpointResponse = Mockery::mock(EndpointResponseInterface::class);
            $endpointResponse
                ->shouldReceive('getStatusCode')
                ->once()
                ->andReturn(
                    $statusCode
                );

            $stream = Stream::create('Response body');
            $endpointResponse
                ->shouldReceive('getBody')
                ->once()
                ->andReturn(
                    $stream
                );

            $response = SimpleResponse::createFromEndpointResponse(
                $endpointResponse
            );

            $this->assertEquals(in_array($code, $successStatusCode), $response->isSuccessful());
        }
    }
}
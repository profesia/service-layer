<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Integration\Facade;

use GuzzleHttp\Client;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Facade\ServiceLayer;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestInterface;

class ServiceLayerTest extends MockeryTestCase
{
    public function testCanExecuteRealRequest(): void
    {
        $responseBody = 'Success response';
        $psrResponse = new Response(
            200,
            ['Content-Type' => ['application/json']],
            Stream::create($responseBody)
        );

        /** @var MockInterface|Client $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(function (RequestInterface $request, array $config) {
                if ($request->getMethod() !== 'POST') {
                    return false;
                }
                
                if ((string)$request->getUri() !== 'https://api.example.com/endpoint') {
                    return false;
                }
                
                if ((string)$request->getBody() !== '{"test": "data"}') {
                    return false;
                }
                
                return true;
            })
            ->andReturn($psrResponse);

        $adapter = new \Profesia\ServiceLayer\Adapter\GuzzleAdapter(
            $client,
            \Profesia\ServiceLayer\Adapter\Config\AdapterConfig::createDefault()
        );
        
        $logger = new \Psr\Log\NullLogger();
        $gatewayLogger = new \Profesia\ServiceLayer\Transport\Logging\CommunicationLogger($logger);
        $gateway = new \Profesia\ServiceLayer\Transport\Gateway($adapter, $gatewayLogger);
        
        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $uri = new Uri('https://api.example.com/endpoint');
        $body = Stream::create('{"test": "data"}');
        $response = $facade->executeRequest($uri, HttpMethod::createPost(), $body);
        
        $this->assertInstanceOf(SimpleResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function testCanExecuteGetRequestWithoutBody(): void
    {
        $responseBody = '{"result": "ok"}';
        $psrResponse = new Response(
            200,
            ['Content-Type' => ['application/json']],
            Stream::create($responseBody)
        );

        /** @var MockInterface|Client $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(function (RequestInterface $request, array $config) {
                return $request->getMethod() === 'GET'
                    && (string)$request->getUri() === 'https://api.example.com/data';
            })
            ->andReturn($psrResponse);

        $adapter = new \Profesia\ServiceLayer\Adapter\GuzzleAdapter(
            $client,
            \Profesia\ServiceLayer\Adapter\Config\AdapterConfig::createDefault()
        );
        
        $gatewayLogger = new \Profesia\ServiceLayer\Transport\Logging\CommunicationLogger(
            new \Psr\Log\NullLogger()
        );
        $gateway = new \Profesia\ServiceLayer\Transport\Gateway($adapter, $gatewayLogger);
        
        $facade = new ServiceLayer($gateway, new Psr17Factory());
        
        $uri = new Uri('https://api.example.com/data');
        $response = $facade->executeRequest($uri, HttpMethod::createGet());
        
        $this->assertInstanceOf(SimpleResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        
        $bodyContent = (string)$response->getResponseBody();
        $this->assertEquals($responseBody, $bodyContent);
    }
}

<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Request;

use GuzzleHttp\Psr7\Uri;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class AbstractGatewayRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCanCreateRequest()
    {
        $headers = [
            'Content-Type' => 'text\html',
            'Test'         => 'abcd'
        ];

        /** @var RequestInterface|MockInterface $psrRequest */
        $psrRequest = Mockery::mock(RequestInterface::class . '[getHeaders]');

        foreach ($headers as $headerName => $headerValue) {
            $psrRequest
                ->shouldReceive('withAddedHeader')
                ->once()
                ->withArgs(
                    [
                        $headerName,
                        $headerValue
                    ]
                )->andReturn($psrRequest);
        }


        $uri = new Uri('https://test.com');

        /** @var RequestFactoryInterface|MockInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $requestFactory
            ->shouldReceive('createRequest')
            ->once()
            ->withArgs(
                [
                    'POST',
                    $uri,
                ]
            )->andReturn(
                $psrRequest
            );

        $gatewayRequest = new class($requestFactory, $uri, $headers) extends AbstractGatewayRequest {
            private UriInterface $uri;
            private array        $headers;

            public function __construct(RequestFactoryInterface $psrRequestFactory, UriInterface $uri, array $headers)
            {
                $this->uri     = $uri;
                $this->headers = $headers;
                parent::__construct($psrRequestFactory);
            }

            protected function getMethod(): HttpMethod
            {
                return HttpMethod::createPost();
            }

            protected function getUri(): UriInterface
            {
                return $this->uri;
            }

            protected function getHeaders(): array
            {
                return $this->headers;
            }

            protected function getBody(): ?StreamInterface
            {
                return null;
            }
        };

        $gatewayRequest->toPsrRequest();
        $this->assertTrue(true);
    }
}

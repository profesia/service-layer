<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Integration\Transport;

use GuzzleHttp\Client;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfigBuilder;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Response\Domain\SimpleResponse;
use Profesia\ServiceLayer\Transport\Logging\DefaultRequestGatewayLogger;
use Profesia\ServiceLayer\Transport\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Transport\Gateway;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RequestGatewayTest extends MockeryTestCase
{
    public function testCanHandleSuccessfulRequest(): void
    {
        $requestMethod     = HttpMethod::createPost();
        $requestUri        = new Uri('https://www.test.com');
        $requestHeaders    = [
            'Test' => [
                'abcd'
            ]
        ];
        $requestBodyString = 'Request body';
        $requestBody       = Stream::create($requestBodyString);

        $statusCode         = 201;
        $responseHeaders    = [
            'Status' => [
                "{$statusCode} Created"
            ]
        ];
        $responseBodyString = 'Test body';
        $responseBody       = Stream::create($responseBodyString);
        $psrResponse        = new Response(
            $statusCode,
            $responseHeaders,
            $responseBody
        );
        $responseHttpCode   = StatusCode::createFromInteger($statusCode);

        $requestFactory = new Psr17Factory();

        $gatewayRequest = (new class($requestMethod, $requestUri, $requestBody, $requestFactory, $requestHeaders) extends AbstractGatewayRequest {

            private HttpMethod $requestMethod;
            private UriInterface $requestUri;
            private StreamInterface $requestBody;

            /** @var string[][] */
            private array $requestHeaders;

            /**
             * @param HttpMethod              $requestMethod  ,
             * @param UriInterface            $requestUri     ,
             * @param StreamInterface         $requestBody    ,
             * @param RequestFactoryInterface $requestFactory ,
             * @param string[][]              $requestHeaders
             */
            public function __construct(
                HttpMethod $requestMethod,
                UriInterface $requestUri,
                StreamInterface $requestBody,
                RequestFactoryInterface $requestFactory,
                array $requestHeaders
            ) {
                $this->requestMethod  = $requestMethod;
                $this->requestUri     = $requestUri;
                $this->requestBody    = $requestBody;
                $this->requestHeaders = $requestHeaders;

                parent::__construct(
                    $requestFactory
                );
            }

            protected function getMethod(): HttpMethod
            {
                return $this->requestMethod;
            }

            protected function getUri(): UriInterface
            {
                return $this->requestUri;
            }

            /**
             * @return string[][]
             */
            protected function getHeaders(): array
            {
                return $this->requestHeaders;
            }

            protected function getBody(): ?StreamInterface
            {
                return $this->requestBody;
            }
        });

        /** @var MockInterface|Client $client */
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('send')
            ->once()
            ->withArgs(
                function (RequestInterface $request, array $requestConfig) use ($requestHeaders, $requestUri, $requestBody) {
                    if ($requestUri !== $request->getUri()) {
                        return false;
                    }

                    if ($requestConfig['headers'] !== $request->getHeaders()) {
                        return false;
                    }

                    if ($request->getBody() !== $requestBody) {
                        return false;
                    }

                    return true;
                }
            )->andReturn(
                $psrResponse
            );

        $configBuilder = GuzzleAdapterConfigBuilder::createDefault();

        $adapter = new GuzzleAdapter(
            $client,
            $configBuilder
        );

        /** @var MockInterface|LoggerInterface $psrLogger */
        $psrLogger = Mockery::mock(LoggerInterface::class);
        $psrLogger
            ->shouldReceive('log')
            ->once()
            ->withArgs(
                function (string $logLevel, string $message, array $context) use (
                    $requestMethod,
                    $requestUri,
                    $requestHeaders,
                    $requestBodyString,
                    $responseHttpCode,
                    $responseHeaders,
                    $responseBodyString
                ) {
                    if ($logLevel !== LogLevel::INFO) {
                        return false;
                    }

                    if ($message !== "{$requestMethod}: {$requestUri}") {
                        return false;
                    }

                    if (!array_key_exists('Request', $context)) {
                        return false;
                    }

                    $requestContext = $context['Request'];
                    $toCompare      = [
                        'Headers' => $requestHeaders,
                        'Body'    => $requestBodyString
                    ];

                    foreach ($toCompare as $key => $value) {
                        if (!array_key_exists($key, $requestContext)) {
                            return false;
                        }

                        if ($requestContext[$key] !== $value) {
                            return false;
                        }
                    }


                    if (!array_key_exists('Response', $context)) {
                        return false;
                    }

                    $responseContext = $context['Response'];
                    $toCompare       = [
                        'Http Code' => $responseHttpCode,
                        'Headers'   => $responseHeaders,
                        'Body'      => $responseBodyString
                    ];

                    foreach ($toCompare as $key => $value) {
                        if (!array_key_exists($key, $responseContext)) {
                            return false;
                        }

                        if ($responseContext[$key] != $value) {
                            return false;
                        }
                    }

                    if (!array_key_exists('Elapsed Time', $context)) {
                        return false;
                    }

                    if ($context['Elapsed Time'] < 0) {
                        return false;
                    }

                    return true;
                }
            );

        $gatewayLogger = new DefaultRequestGatewayLogger(
            $psrLogger
        );

        $endpointResponse = EndpointResponse::createFromPsrResponse($psrResponse);
        $domainResponse   = SimpleResponse::createFromEndpointResponse($endpointResponse);

        $gateway = new Gateway(
            $adapter,
            $gatewayLogger
        );

        $response = $gateway->sendRequest($gatewayRequest);
        $this->assertEquals($domainResponse, $response);

        /** @var StreamInterface $responseBody */
        $responseBody = $response->getResponseBody();
        $this->assertEquals($responseBodyString, $responseBody->getContents());
    }
}

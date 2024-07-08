<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Psr\Http\Client\ClientExceptionInterface;

final class GuzzleAdapter implements AdapterInterface
{
    private Client $client;
    /** @var array<mixed>  */
    private array  $config;

    public function __construct(
        Client $client,
        AdapterConfigInterface $configBuilder
    ) {
        $this->client = $client;
        $this->config = $configBuilder->getConfig();
    }

    /**
     * @inheritdoc
     */
    public function send(GatewayRequestInterface $request, ?AdapterConfigInterface $configOverrideBuilder = null): EndpointResponse
    {
        try {
            $psrRequest  = $request->toPsrRequest();
            $finalConfig = $this->config;
            if ($configOverrideBuilder !== null) {
                $finalConfig = array_merge(
                    $finalConfig,
                    $configOverrideBuilder->getConfig()
                );
            }

            $finalConfig = array_merge(
                $finalConfig,
                [
                    RequestOptions::HEADERS => array_merge(
                        $finalConfig[RequestOptions::HEADERS] ?? [],
                        $psrRequest->getHeaders(),
                    )
                ]
            );

            return EndpointResponse::createFromPsrResponse(
                $this->client->send(
                    $psrRequest,
                    $finalConfig
                )
            );
        } catch (RequestException $e) {
            if ($e->getResponse() === null) {
                throw new AdapterException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e->getPrevious()
                );
            }

            return EndpointResponse::createFromPsrResponse(
                $e->getResponse()
            );
        } catch (ClientExceptionInterface $e) {
            throw new AdapterException(
                $e->getMessage(),
                $e->getCode(),
                $e->getPrevious()
            );
        }
    }
}

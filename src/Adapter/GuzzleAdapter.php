<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\Config\GuzzleConfigTransformer;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponse;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Psr\Http\Client\ClientExceptionInterface;

final class GuzzleAdapter implements AdapterInterface
{
    private Client $client;
    private AdapterConfigInterface $config;

    public function __construct(
        Client                 $client,
        AdapterConfigInterface $configBuilder
    )
    {
        $this->client = $client;
        $this->config = $configBuilder;
    }

    /**
     * @inheritdoc
     */
    public function send(GatewayRequestInterface $request, ?AdapterConfigInterface $configBuilderOverride = null): EndpointResponse
    {
        try {
            $psrRequest = $request->toPsrRequest();
            $finalConfig = $this->config;
            if ($configBuilderOverride !== null) {
                $finalConfig = $finalConfig->merge($configBuilderOverride);
            }

            return EndpointResponse::createFromPsrResponse(
                $this->client->send(
                    $psrRequest,
                    GuzzleConfigTransformer::transform($finalConfig->merge(
                        AdapterConfig::createFromArray(
                            [
                                AdapterConfigInterface::HEADERS => $psrRequest->getHeaders(),
                            ]
                        )
                    ))
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

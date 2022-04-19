<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Response\Domain;

use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\StreamInterface;

final class SimpleResponse implements GatewayDomainResponseInterface
{
    private StatusCode      $statusCode;
    private StreamInterface $responseBody;

    private function __construct(StatusCode $statusCode, StreamInterface $responseBody)
    {
        $this->statusCode   = $statusCode;
        $this->responseBody = $responseBody;
    }

    public static function createFromStatusCodeAndStream(StatusCode $statusCode, StreamInterface $responseBody): self
    {
        return new self(
            $statusCode,
            $responseBody
        );
    }

    public static function createFromEndpointResponse(EndpointResponseInterface $endpointResponse): self
    {
        return new self(
            $endpointResponse->getStatusCode(),
            $endpointResponse->getBody()
        );
    }

    public function getResponseBody(): StreamInterface
    {
        return $this->responseBody;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode->isSuccess();
    }
}

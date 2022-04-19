<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Response\Connection;

use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class EndpointResponse implements EndpointResponseInterface
{
    private StatusCode      $statusCode;
    private StreamInterface $body;

    /** @var string[][] */
    private array $headers;

    /**
     * @param StatusCode      $statusCode
     * @param StreamInterface $body
     * @param string[][]      $headers
     */
    private function __construct(StatusCode $statusCode, StreamInterface $body, array $headers)
    {
        $this->statusCode = $statusCode;
        $this->body       = $body;
        $this->headers    = $headers;
    }

    public static function createFromPsrResponse(ResponseInterface $response): self
    {
        return new self(
            StatusCode::createFromInteger(
                $response->getStatusCode()
            ),
            $response->getBody(),
            $response->getHeaders()
        );
    }

    /**
     * @param StatusCode      $statusCode
     * @param StreamInterface $body
     * @param string[][]      $headers
     *
     * @return self
     */
    public static function createFromComponents(StatusCode $statusCode, StreamInterface $body, array $headers): self
    {
        return new self(
            $statusCode,
            $body,
            $headers
        );
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode->isSuccess();
    }

    public function getStatusCode(): StatusCode
    {
        return $this->statusCode;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}

<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Response\Connection;

use Profesia\ServiceLayer\Response\GatewayResponseInterface;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use Psr\Http\Message\StreamInterface;

interface EndpointResponseInterface extends GatewayResponseInterface
{
    public function getStatusCode(): StatusCode;

    public function getBody(): StreamInterface;

    /**
     * @return string[][]
     */
    public function getHeaders(): array;
}

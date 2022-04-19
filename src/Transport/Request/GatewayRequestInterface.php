<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

interface GatewayRequestInterface
{
    public function toPsrRequest(): RequestInterface;

    public function getCensoredBody(): ?StreamInterface;

    /**
     * @return string[][]
     */
    public function getCensoredHeaders(): array;

    public function getCensoredUri(): UriInterface;
}

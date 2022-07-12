<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Request\Factory;

use Psr\Http\Message\RequestFactoryInterface;

abstract class AbstractGatewayRequestFactory
{
    private RequestFactoryInterface $psrRequestFactory;

    public function __construct(RequestFactoryInterface $psrRequestFactory)
    {
        $this->psrRequestFactory = $psrRequestFactory;
    }

    protected function getPsrRequestFactory(): RequestFactoryInterface
    {
        return $this->psrRequestFactory;
    }
}

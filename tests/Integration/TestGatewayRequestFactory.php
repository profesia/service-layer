<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Integration;

use Profesia\ServiceLayer\Request\Factory\AbstractGatewayRequestFactory;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;

class TestGatewayRequestFactory extends AbstractGatewayRequestFactory
{
    public function create(): GatewayRequestInterface
    {
        return new TestRequest1(
            $this->getPsrRequestFactory()
        );
    }
}

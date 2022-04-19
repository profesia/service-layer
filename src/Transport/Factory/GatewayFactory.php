<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Factory;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Transport\Logging\RequestGatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Gateway;

class GatewayFactory
{
    public function createGateway(AdapterInterface $client, RequestGatewayLoggerInterface $gatewayLogger): Gateway
    {
        return new Gateway(
            $client,
            $gatewayLogger
        );
    }
}

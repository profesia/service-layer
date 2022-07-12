<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Factory;

use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Transport\Gateway;

class GatewayFactory
{
    public function createGateway(AdapterInterface $client, GatewayLoggerInterface $gatewayLogger): Gateway
    {
        return new Gateway(
            $client,
            $gatewayLogger
        );
    }
}

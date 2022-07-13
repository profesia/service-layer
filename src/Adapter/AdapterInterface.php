<?php
declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter;

use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;

interface AdapterInterface
{
    /**
     * @param GatewayRequestInterface     $request
     * @param AdapterConfigInterface|null $configOverrideBuilder
     *
     * @return EndpointResponseInterface
     * @throws AdapterException
     */
    public function send(GatewayRequestInterface $request, ?AdapterConfigInterface $configOverrideBuilder = null): EndpointResponseInterface;
}

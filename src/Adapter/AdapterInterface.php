<?php
declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter;

use Profesia\ServiceLayer\Adapter\Config\AdapterConfigBuilderInterface;
use Profesia\ServiceLayer\Exception\AdapterException;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;

interface AdapterInterface
{
    /**
     * @param GatewayRequestInterface            $request
     * @param AdapterConfigBuilderInterface|null $configOverrideBuilder
     *
     * @return EndpointResponseInterface
     * @throws AdapterException
     */
    public function send(GatewayRequestInterface $request, ?AdapterConfigBuilderInterface $configOverrideBuilder = null): EndpointResponseInterface;
}

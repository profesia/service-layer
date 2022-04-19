<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Response\Domain;

use Profesia\ServiceLayer\Response\GatewayResponseInterface;

interface GatewayDomainResponseInterface extends GatewayResponseInterface
{
    /**
     * @return mixed
     */
    public function getResponseBody();
}

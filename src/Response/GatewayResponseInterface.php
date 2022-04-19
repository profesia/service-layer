<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Response;

interface GatewayResponseInterface
{
    public function isSuccessful(): bool;
}

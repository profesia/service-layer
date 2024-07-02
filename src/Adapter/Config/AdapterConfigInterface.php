<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

interface AdapterConfigInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}

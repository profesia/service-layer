<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

interface AdapterConfigBuilderInterface
{
    public function getConfig(): array;
}

<?php
declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

interface AdapterConfigInterface
{
    public const TIMEOUT         = 'timeout';
    public const CONNECT_TIMEOUT = 'connect_timeout';
    public const VERIFY          = 'verify';
    public const ALLOW_REDIRECTS = 'allow_redirects';
    public const AUTH            = 'auth';

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}

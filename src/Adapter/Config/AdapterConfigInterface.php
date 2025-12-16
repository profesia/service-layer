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
    public const HEADERS         = 'headers';

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Merge configurations. The provided config overrides existing values.
     * Performs shallow merge, not deep merge.
     *
     * @param AdapterConfigInterface $config Configuration to merge
     * @return self New instance with merged configuration
     */
    public function merge(AdapterConfigInterface $config): self;
}

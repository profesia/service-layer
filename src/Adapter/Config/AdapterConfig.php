<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use InvalidArgumentException;

/**
 * Platform-independent adapter configuration.
 * Holds configuration using standard keys defined in AdapterConfigInterface.
 */
final class AdapterConfig implements AdapterConfigInterface
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create configuration from array using platform-independent keys
     *
     * @param array<string, mixed> $config Configuration array with keys from AdapterConfigInterface
     * @return self
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $config): self
    {
        self::validateConfig($config);
        return new self($config);
    }

    /**
     * Create default empty configuration
     *
     * @return self
     */
    public static function createDefault(): self
    {
        return new self([]);
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Validate configuration array
     *
     * @param array<string, mixed> $config
     * @throws InvalidArgumentException
     */
    private static function validateConfig(array $config): void
    {
        if (array_key_exists(self::TIMEOUT, $config)) {
            if (!is_float($config[self::TIMEOUT]) && !is_int($config[self::TIMEOUT])) {
                throw new InvalidArgumentException('Timeout value should be a valid number');
            }
        }

        if (array_key_exists(self::CONNECT_TIMEOUT, $config)) {
            if (!is_float($config[self::CONNECT_TIMEOUT]) && !is_int($config[self::CONNECT_TIMEOUT])) {
                throw new InvalidArgumentException('Connect timeout value should be a valid number');
            }
        }

        if (array_key_exists(self::VERIFY, $config)) {
            if (!is_bool($config[self::VERIFY]) && !is_string($config[self::VERIFY])) {
                throw new InvalidArgumentException('Verify value should be a valid boolean or a string path');
            }
        }

        if (array_key_exists(self::ALLOW_REDIRECTS, $config)) {
            if (!is_bool($config[self::ALLOW_REDIRECTS])) {
                throw new InvalidArgumentException('Allow redirects value should be a valid boolean');
            }
        }

        if (array_key_exists(self::AUTH, $config)) {
            if (!is_array($config[self::AUTH])) {
                throw new InvalidArgumentException('Auth value should be a valid array');
            }
            if (count($config[self::AUTH]) < 2) {
                throw new InvalidArgumentException('Auth value requires at least two items in the array config');
            }
        }

        if (array_key_exists(self::HEADERS, $config)) {
            if (!is_array($config[self::HEADERS])) {
                throw new InvalidArgumentException('Headers value should be a valid array');
            }
        }
    }
}

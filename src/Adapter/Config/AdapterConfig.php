<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use InvalidArgumentException;
use Profesia\ServiceLayer\ValueObject\Login;
use Profesia\ServiceLayer\ValueObject\Password;
use Profesia\ServiceLayer\ValueObject\Timeout;

/**
 * Platform-independent adapter configuration.
 * Holds configuration using standard keys defined in AdapterConfigInterface.
 * Stores values as value objects for type safety.
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
        $normalizedConfig = self::normalizeConfig($config);
        return new self($normalizedConfig);
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
     * @inheritDoc
     */
    public function merge(AdapterConfigInterface $config): self
    {
        $baseConfig = $this->config;
        $newConfig = $config->getConfig();
        
        // Shallow merge - override values, not deep merge
        $mergedConfig = array_merge($baseConfig, $newConfig);
        
        // Deep merge for HEADERS key specifically
        if (array_key_exists(self::HEADERS, $baseConfig) && array_key_exists(self::HEADERS, $newConfig)) {
            if (is_array($baseConfig[self::HEADERS]) && is_array($newConfig[self::HEADERS])) {
                $mergedConfig[self::HEADERS] = array_merge($baseConfig[self::HEADERS], $newConfig[self::HEADERS]);
            }
        }
        
        return new self($mergedConfig);
    }

    /**
     * Normalize and validate configuration array, converting to value objects
     *
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    private static function normalizeConfig(array $config): array
    {
        $normalized = [];

        // Normalize timeout to Timeout value object
        if (array_key_exists(self::TIMEOUT, $config)) {
            if (!is_float($config[self::TIMEOUT]) && !is_int($config[self::TIMEOUT])) {
                throw new InvalidArgumentException('Timeout value should be a valid number');
            }
            $normalized[self::TIMEOUT] = Timeout::createFromFloat((float)$config[self::TIMEOUT]);
        }

        // Normalize connect_timeout to Timeout value object
        if (array_key_exists(self::CONNECT_TIMEOUT, $config)) {
            if (!is_float($config[self::CONNECT_TIMEOUT]) && !is_int($config[self::CONNECT_TIMEOUT])) {
                throw new InvalidArgumentException('Connect timeout value should be a valid number');
            }
            $normalized[self::CONNECT_TIMEOUT] = Timeout::createFromFloat((float)$config[self::CONNECT_TIMEOUT]);
        }

        // Normalize verify (keep as-is, bool or string)
        if (array_key_exists(self::VERIFY, $config)) {
            if (!is_bool($config[self::VERIFY]) && !is_string($config[self::VERIFY])) {
                throw new InvalidArgumentException('Verify value should be a valid boolean or a string path');
            }
            $normalized[self::VERIFY] = $config[self::VERIFY];
        }

        // Normalize allow_redirects (keep as-is, bool)
        if (array_key_exists(self::ALLOW_REDIRECTS, $config)) {
            if (!is_bool($config[self::ALLOW_REDIRECTS])) {
                throw new InvalidArgumentException('Allow redirects value should be a valid boolean');
            }
            $normalized[self::ALLOW_REDIRECTS] = $config[self::ALLOW_REDIRECTS];
        }

        // Normalize auth to use Login and Password value objects
        if (array_key_exists(self::AUTH, $config)) {
            if (!is_array($config[self::AUTH])) {
                throw new InvalidArgumentException('Auth value should be a valid array');
            }
            if (count($config[self::AUTH]) < 2) {
                throw new InvalidArgumentException('Auth value requires at least two items in the array config');
            }
            
            $authConfig = [
                Login::createFromString($config[self::AUTH][0]),
                Password::createFromString($config[self::AUTH][1]),
            ];
            
            // Optional third parameter (e.g., 'digest')
            if (count($config[self::AUTH]) >= 3) {
                $authConfig[] = $config[self::AUTH][2];
            }
            
            $normalized[self::AUTH] = $authConfig;
        }

        // Normalize headers (keep as-is, array)
        if (array_key_exists(self::HEADERS, $config)) {
            if (!is_array($config[self::HEADERS])) {
                throw new InvalidArgumentException('Headers value should be a valid array');
            }
            $normalized[self::HEADERS] = $config[self::HEADERS];
        }

        // Pass through any other options
        foreach ($config as $key => $value) {
            if (!array_key_exists($key, $normalized)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}

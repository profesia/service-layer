<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use InvalidArgumentException;
use Profesia\ServiceLayer\ValueObject\Login;
use Profesia\ServiceLayer\ValueObject\Password;
use Profesia\ServiceLayer\ValueObject\Timeout;

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
        // Normalize timeout - create Timeout value object and convert back to float
        if (array_key_exists(self::TIMEOUT, $config)) {
            /** @phpstan-ignore-next-line  */
            $config[self::TIMEOUT] = (Timeout::createFromFloat($config[self::TIMEOUT]))->toFloat();
        }

        // Normalize connect_timeout - create Timeout value object and convert back to float
        if (array_key_exists(self::CONNECT_TIMEOUT, $config)) {
            /** @phpstan-ignore-next-line  */
            $config[self::CONNECT_TIMEOUT] = (Timeout::createFromFloat($config[self::CONNECT_TIMEOUT]))->toFloat();
        }

        // Normalize verify (keep as-is, bool or string)
        if (array_key_exists(self::VERIFY, $config)) {
            if (!is_bool($config[self::VERIFY]) && !is_string($config[self::VERIFY])) {
                throw new InvalidArgumentException('Verify value should be a valid boolean or a string path');
            }
        }

        // Normalize allow_redirects (keep as-is, bool)
        if (array_key_exists(self::ALLOW_REDIRECTS, $config)) {
            if (!is_bool($config[self::ALLOW_REDIRECTS])) {
                throw new InvalidArgumentException('Allow redirects value should be a valid boolean');
            }
        }

        // Normalize auth - create Login and Password value objects and convert back to strings
        if (array_key_exists(self::AUTH, $config)) {
            if (!is_array($config[self::AUTH])) {
                throw new InvalidArgumentException('Auth value should be a valid array');
            }
            if (count($config[self::AUTH]) < 2) {
                throw new InvalidArgumentException('Auth value requires at least two items in the array config');
            }


            $authConfig = [
                (Login::createFromString($config[self::AUTH][0]))->toString(),
                (Password::createFromString($config[self::AUTH][1]))->toString(),
            ];

            // Optional third parameter (e.g., 'digest')
            if (count($config[self::AUTH]) >= 3) {
                $authConfig[] = $config[self::AUTH][2];
            }

            $config[self::AUTH] = $authConfig;
        }

        // Normalize headers (keep as-is, array)
        if (array_key_exists(self::HEADERS, $config)) {
            if (!is_array($config[self::HEADERS])) {
                throw new InvalidArgumentException('Headers value should be a valid array');
            }
        }

        return new self($config);
    }

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
            /** @phpstan-ignore-next-line  */
            $mergedConfig[self::HEADERS] = array_merge_recursive($baseConfig[self::HEADERS], $newConfig[self::HEADERS]);
        }
        
        return new self($mergedConfig);
    }
}

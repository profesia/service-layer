<?php
declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use InvalidArgumentException;

abstract class AbstractAdapterConfig implements AdapterConfigInterface
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    final protected function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return static
     * @throws InvalidArgumentException
     */
    abstract public static function createFromArray(array $config): self;

    public static function createDefault(): self
    {
        return new static(
            []
        );
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
        
        return new static($mergedConfig);
    }
}

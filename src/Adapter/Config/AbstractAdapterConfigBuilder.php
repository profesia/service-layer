<?php
declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use InvalidArgumentException;

abstract class AbstractAdapterConfigBuilder implements AdapterConfigBuilderInterface
{
    private array $config;

    final protected function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $config
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

    public function getConfig(): array
    {
        return $this->config;
    }
}

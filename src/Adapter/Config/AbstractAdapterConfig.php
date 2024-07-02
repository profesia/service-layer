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
}

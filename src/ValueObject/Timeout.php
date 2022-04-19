<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\ValueObject;

use InvalidArgumentException;

final class Timeout
{
    public const VALUE_INDEFINITELY = 0.0;

    private float $value;

    private function __construct(float $value)
    {
        $this->value = $value;
    }

    public static function createFromFloat(float $value): Timeout
    {
        if ($value < self::VALUE_INDEFINITELY) {
            $formattedNumber = number_format($value, 1);

            throw new InvalidArgumentException("Timeout value should be 0.0 or greater. Supplied value: [{$formattedNumber}]");
        }

        return new self($value);
    }

    public static function createIndefinitely(): Timeout
    {
        return new self(self::VALUE_INDEFINITELY);
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2);
    }

    public function isIndefinitely(): bool
    {
        return ($this->value === self::VALUE_INDEFINITELY);
    }
}

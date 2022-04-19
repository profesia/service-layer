<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\ValueObject;

use InvalidArgumentException;

class Password
{
    private string $password;

    private function __construct(string $password)
    {
        $this->password = $password;
    }

    public static function createFromString(string $password): Password
    {
        if ($password === '') {
            throw new InvalidArgumentException('Password could not be a blank string');
        }

        return new self($password);
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return $this->password;
    }
}

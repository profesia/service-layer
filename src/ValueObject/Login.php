<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\ValueObject;

use InvalidArgumentException;

final class Login
{
    private string $login;

    private function __construct(string $login)
    {
        $this->login = $login;
    }

    public static function createFromString(string $login): Login
    {
        if ($login === '') {
            throw new InvalidArgumentException('Login could not be a blank string');
        }

        return new self($login);
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString()
    {
        return $this->login;
    }
}

<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Response\Domain;

use Throwable;

final class ErrorResponse implements DomainResponseInterface
{
    private Throwable $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    public function getResponseBody(): string
    {
        return $this->throwable->getMessage();
    }
}

<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Response\Domain;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Profesia\ServiceLayer\Response\Domain\ErrorResponse;

class ErrorResponseTest extends MockeryTestCase
{
    public function testCanDetectFailure()
    {
        /** @var Exception|MockInterface $throwable */
        $throwable = Mockery::mock(Exception::class);

        $errorResponse = new ErrorResponse(
            $throwable
        );

        $this->assertFalse($errorResponse->isSuccessful());
    }
}
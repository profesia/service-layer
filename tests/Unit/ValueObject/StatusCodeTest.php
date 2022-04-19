<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\ValueObject;

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\ValueObject\StatusCode;
use TypeError;

class StatusCodeTest extends MockeryTestCase
{
    /**
     * @group value-object
     */
    public function testCanThrowAnErrorOnBadType()
    {
        $this->expectException(TypeError::class);
        StatusCode::createFromInteger('test');
    }

    /**
     * @group value-object
     */
    public function testCanThrowAnExceptionOnLowerThan100UnsupportedCode()
    {
        $this->expectException(InvalidArgumentException::class);
        StatusCode::createFromInteger(99);
    }

    /**
     * @group value-object
     */
    public function testCanThrowAnExceptionOnGreaterThan599UnsupportedCode()
    {
        $this->expectException(InvalidArgumentException::class);
        StatusCode::createFromInteger(600);
    }

    /**
     * @group value-object
     * @dataProvider standardStatusCodesDataProvider
     */
    public function testCanCreateInstanceWithStandardCode(int $code, bool $isSuccess)
    {
        $statusCode = StatusCode::createFromInteger($code);
        $this->assertEquals($isSuccess, $statusCode->isSuccess());
    }

    /**
     * @group value-object
     */
    public function testCanCreateInstanceWithNonStandardSuccessCode()
    {
        $statusCode = StatusCode::createFromInteger(299);
        $this->assertTrue($statusCode->isSuccess());
    }

    /**
     * @group value-object
     */
    public function testCanCreateInstanceWithNonStandardErrorCode()
    {
        $statusCode = StatusCode::createFromInteger(520);
        $this->assertFalse($statusCode->isSuccess());
    }

    /**
     * @group value-object
     */
    public function testCanCreateAndWorkWith()
    {
        $httpCode = StatusCode::createFromInteger(StatusCode::HTTP_CODE_OK);
        $this->assertInstanceOf(StatusCode::class, $httpCode);
        $this->assertEquals(StatusCode::HTTP_CODE_OK, (string)$httpCode->toString());

        $this->assertIsString($httpCode->toString());
        $this->assertEquals((string)StatusCode::HTTP_CODE_OK, $httpCode->toString());

        $this->assertIsString((string)$httpCode);
        $this->assertEquals((string)StatusCode::HTTP_CODE_OK, (string)$httpCode);

        $this->assertTrue($httpCode->isSuccess());
        $this->assertFalse((StatusCode::createFromInteger(StatusCode::HTTP_CODE_INTERNAL_SERVER_ERROR))->isSuccess());
    }

    /**
     * @group value-object
     */
    public function testEquality()
    {
        $httpCode1 = StatusCode::createFromInteger(StatusCode::HTTP_CODE_OK);
        $httpCode2 = StatusCode::createFromInteger(StatusCode::HTTP_CODE_OK);
        $httpCode3 = StatusCode::createFromInteger(StatusCode::HTTP_CODE_ACCEPTED);

        $this->assertTrue($httpCode1->equals($httpCode2));
        $this->assertTrue($httpCode1->equalsWithInt(StatusCode::HTTP_CODE_OK));
        $this->assertFalse($httpCode1->equals($httpCode3));
        $this->assertFalse($httpCode1->equalsWithInt(StatusCode::HTTP_CODE_ACCEPTED));
    }

    /**
     * @group value-object
     */
    public function testListOfSuccessCodes()
    {
        $listOfSuccessCodes = StatusCode::getListOfSuccessStatusCodes();
        foreach (StatusCode::getListOfSuccessStatusCodes() as $statusCode) {
            $isSuccess   = in_array($statusCode, $listOfSuccessCodes);
            $valueObject = StatusCode::createFromInteger($statusCode);

            $this->assertEquals($isSuccess, $valueObject->isSuccess());
        }
    }

    public function standardStatusCodesDataProvider(): array
    {
        $successStatusCodes = StatusCode::getListOfSuccessStatusCodes();

        return array_map(
            static fn(int $code): array => [$code, in_array($code, $successStatusCodes, true)],
            StatusCode::getListOfStatusCodes()
        );
    }
}

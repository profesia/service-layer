<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\ValueObject;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\ValueObject\Password;
use TypeError;
use InvalidArgumentException;

class PasswordTest extends MockeryTestCase
{
    /**
     * @group value-object
     */
    public function testCanThrowAnErrorOnBadType()
    {
        $this->expectException(TypeError::class);
        Password::createFromString(1);
    }

    /**
     * @group value-object
     */
    public function testCanThrowAnExceptionEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        Password::createFromString('');
    }

    /**
     * @group value-object
     */
    public function testCanCreateAndWorkWith()
    {
        $password = Password::createFromString('test');
        $this->assertInstanceOf(Password::class, $password);
        $this->assertIsString($password->toString());
        $this->assertEquals('test', $password->toString());

        $this->assertIsString((string)$password);
        $this->assertEquals('test', (string)$password);
    }
}

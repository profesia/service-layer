<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\ValueObject;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\ValueObject\Login;
use TypeError;
use InvalidArgumentException;

class LoginTest extends MockeryTestCase
{
    /**
     * @group value-object
     */
    public function testCanThrowAnErrorOnBadType()
    {
        $this->expectException(TypeError::class);
        Login::createFromString(1);
    }

    /**
     * @group value-object
     */
    public function testCanThrowAnExceptionEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        Login::createFromString('');
    }

    /**
     * @group value-object
     */
    public function testCanCreateAndWorkWith()
    {
        $login = Login::createFromString('test');
        $this->assertInstanceOf(Login::class, $login);
        $this->assertIsString($login->toString());
        $this->assertEquals('test', $login->toString());

        $this->assertIsString((string)$login);
        $this->assertEquals('test', (string)$login);
    }
}

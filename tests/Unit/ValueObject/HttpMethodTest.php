<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\ValueObject;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use TypeError;
use InvalidArgumentException;

class HttpMethodTest extends MockeryTestCase
{
    public function testCanThrowAnErrorOnBadType()
    {
        $this->expectException(TypeError::class);
        HttpMethod::createFromString(1);
    }

    public function testCanThrowAnExceptionOnUnsupportedMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        HttpMethod::createFromString('');
    }

    public function testCanCreateAndWorkWith()
    {
        $httpMethod = HttpMethod::createFromString(HttpMethod::HTTP_METHOD_GET);
        $this->assertInstanceOf(HttpMethod::class, $httpMethod);
        $this->assertIsString($httpMethod->toString());
        $this->assertEquals(HttpMethod::HTTP_METHOD_GET, $httpMethod->toString());

        $this->assertIsString((string)$httpMethod);
        $this->assertEquals(HttpMethod::HTTP_METHOD_GET, (string)$httpMethod);
    }

    public function testFactoryMethods(): void
    {
        $methods = [
            'get',
            'post',
            'put',
            'delete'
        ];

        foreach ($methods as $method) {
            $uMethod = ucfirst($method);
            /** @var HttpMethod $httpMethod */
            $class = HttpMethod::class;
            $httpMethod = call_user_func("{$class}::create{$uMethod}");
            $upperMethod = strtoupper($method);
            $this->assertEquals($upperMethod, $httpMethod->toString());
        }
    }
}

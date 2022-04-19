<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\ValueObject;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\ValueObject\Timeout;
use TypeError;
class TimeoutTest extends MockeryTestCase
{
    /**
     * @group value-object
     */
    public function testCanThrowAnErrorOnBadType()
    {
        $this->expectException(TypeError::class);
        Timeout::createFromFloat('test');
    }

    /**
     * @group value-object
     */
    public function testCanCreateAndWorkWith()
    {
        $timeout = Timeout::createFromFloat(23.3);
        $this->assertInstanceOf(Timeout::class, $timeout);
        $this->assertIsFloat($timeout->toFloat());
        $this->assertEquals(23.3, $timeout->toFloat());

        $this->assertIsString($timeout->toString());
        $this->assertEquals('23.30', $timeout->toString());

        $this->assertIsString((string)$timeout);
        $this->assertEquals('23.30', (string)$timeout);

        $timeout = Timeout::createFromFloat(23.0);
        $this->assertIsString($timeout->toString());
        $this->assertEquals('23.00', $timeout->toString());

        $this->assertIsString((string)$timeout);
        $this->assertEquals('23.00', (string)$timeout);

        $this->assertFalse($timeout->isIndefinitely());
        $this->assertTrue((Timeout::createIndefinitely())->isIndefinitely());
        $this->assertTrue((Timeout::createFromFloat(Timeout::VALUE_INDEFINITELY))->isIndefinitely());
    }
}

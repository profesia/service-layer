<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Adapter\Config;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;

class GuzzleAdapterConfigTest extends MockeryTestCase
{
    public function testCanTransformToConfig()
    {
        $config = [
            RequestOptions::ALLOW_REDIRECTS => true,
        ];

        $builder = GuzzleAdapterConfig::createFromArray($config);
        $this->assertEquals($config, $builder->getConfig());

        $config  = [
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::VERIFY          => false,
        ];
        $builder = GuzzleAdapterConfig::createFromArray($config);
        $this->assertEquals($config, $builder->getConfig());

        $config  = [
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::VERIFY          => false,
            RequestOptions::CONNECT_TIMEOUT => 5.7,
        ];
        $builder = GuzzleAdapterConfig::createFromArray($config);
        $this->assertEquals($config, $builder->getConfig());

        $config  = [
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::VERIFY          => false,
            RequestOptions::TIMEOUT         => 0.0,
            RequestOptions::CONNECT_TIMEOUT => 5.7,
        ];
        $builder = GuzzleAdapterConfig::createFromArray($config);
        $this->assertEquals($config, $builder->getConfig());

        $config  = [
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::VERIFY          => false,
            RequestOptions::TIMEOUT         => 0.0,
            RequestOptions::CONNECT_TIMEOUT => 5.7,
            RequestOptions::AUTH            => [
                'test',
                'secret',
            ],
        ];
        $builder = GuzzleAdapterConfig::createFromArray($config);
        $this->assertEquals($config, $builder->getConfig());

        $config  = [
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::VERIFY          => false,
            RequestOptions::TIMEOUT         => 0.0,
            RequestOptions::CONNECT_TIMEOUT => 5.7,
            RequestOptions::AUTH            => [
                'test',
                'secret',
                'third'
            ],
        ];
        $builder = GuzzleAdapterConfig::createFromArray($config);
        $this->assertEquals($config, $builder->getConfig());
    }

    public function testWillDetectInvalidTimeout()
    {
        $config = [
            RequestOptions::TIMEOUT => -1.0,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Timeout value should be 0.0 or greater. Supplied value: [-1.0]");
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testWillDetectInvalidConnectionTimeout()
    {
        $config = [
            RequestOptions::CONNECT_TIMEOUT => -10.5,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Timeout value should be 0.0 or greater. Supplied value: [-10.5]");
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testWillDetectInvalidVerify()
    {
        $config = [
            RequestOptions::VERIFY => 1,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Verify value should be a valid boolean or a string path');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testWillDetectInvalidAllowRedirects()
    {
        $config = [
            RequestOptions::ALLOW_REDIRECTS => 10,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Allow redirects value should be a valid boolean');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testWillDetectIncompleteAuthConfig()
    {
        $config = [
            RequestOptions::AUTH => [
                'test',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auth value requires at least two item in the array config');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testWillDetectBlankPassword()
    {
        $config = [
            RequestOptions::AUTH => [
                'login',
                '',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password could not be a blank string');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testWillDetectBlankLogin()
    {
        $config = [
            RequestOptions::AUTH => [
                '',
                'password',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Login could not be a blank string');
        GuzzleAdapterConfig::createFromArray($config);
    }
}

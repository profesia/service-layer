<?php
declare(strict_types=1);

namespace Profesia\ServiceLayer\Test\Unit\Adapter\Config;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfigInterface;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;

class GuzzleAdapterConfigTest extends MockeryTestCase
{
    public function provideDataForTransformation(): array
    {
        return [
            '1 config'                => [
                [
                    AdapterConfigInterface::ALLOW_REDIRECTS => true,
                ],
                [
                    RequestOptions::ALLOW_REDIRECTS => true
                ]
            ],
            '2 configs'               => [
                [
                    AdapterConfigInterface::ALLOW_REDIRECTS => true,
                    AdapterConfigInterface::VERIFY          => false,
                ],
                [
                    RequestOptions::ALLOW_REDIRECTS => true,
                    RequestOptions::VERIFY          => false,
                ]
            ],
            '3 configs'               => [
                [
                    AdapterConfigInterface::ALLOW_REDIRECTS => true,
                    AdapterConfigInterface::VERIFY          => false,
                    AdapterConfigInterface::CONNECT_TIMEOUT => 5.7,
                ],
                [
                    RequestOptions::ALLOW_REDIRECTS => true,
                    RequestOptions::VERIFY          => false,
                    RequestOptions::CONNECT_TIMEOUT => 5.7,
                ]
            ],
            '4 configs'               => [
                [
                    AdapterConfigInterface::ALLOW_REDIRECTS => true,
                    AdapterConfigInterface::VERIFY          => false,
                    AdapterConfigInterface::CONNECT_TIMEOUT => 5.7,
                    AdapterConfigInterface::TIMEOUT         => 0.0,
                ],
                [
                    RequestOptions::ALLOW_REDIRECTS => true,
                    RequestOptions::VERIFY          => false,
                    RequestOptions::TIMEOUT         => 0.0,
                    RequestOptions::CONNECT_TIMEOUT => 5.7,
                ]
            ],
            '5 configs'               => [
                [
                    AdapterConfigInterface::ALLOW_REDIRECTS => true,
                    AdapterConfigInterface::VERIFY          => false,
                    AdapterConfigInterface::CONNECT_TIMEOUT => 5.7,
                    AdapterConfigInterface::TIMEOUT         => 0.0,
                    AdapterConfigInterface::AUTH            => [
                        'test',
                        'secret',
                    ],
                ],
                [
                    RequestOptions::ALLOW_REDIRECTS => true,
                    RequestOptions::VERIFY          => false,
                    RequestOptions::TIMEOUT         => 0.0,
                    RequestOptions::CONNECT_TIMEOUT => 5.7,
                    RequestOptions::AUTH            => [
                        'test',
                        'secret',
                    ],
                ]
            ],
            '5 configs extended auth' => [
                [
                    AdapterConfigInterface::ALLOW_REDIRECTS => true,
                    AdapterConfigInterface::VERIFY          => false,
                    AdapterConfigInterface::CONNECT_TIMEOUT => 5.7,
                    AdapterConfigInterface::TIMEOUT         => 0.0,
                    AdapterConfigInterface::AUTH            => [
                        'test',
                        'secret',
                        'third'
                    ],
                ],
                [
                    RequestOptions::ALLOW_REDIRECTS => true,
                    RequestOptions::VERIFY          => false,
                    RequestOptions::TIMEOUT         => 0.0,
                    RequestOptions::CONNECT_TIMEOUT => 5.7,
                    RequestOptions::AUTH            => [
                        'test',
                        'secret',
                        'third'
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider provideDataForTransformation
     */
    public function testCanTransformToConfig(array $inputConfig, array $configToCompare)
    {
        $builder = GuzzleAdapterConfig::createFromArray($inputConfig);
        $this->assertEquals($configToCompare, $builder->getConfig());
    }

    public function testCanDetectInvalidTimeout()
    {
        $config = [
            AdapterConfigInterface::TIMEOUT => -1.0,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Timeout value should be 0.0 or greater. Supplied value: [-1.0]");
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectInvalidConnectionTimeout()
    {
        $config = [
            AdapterConfigInterface::CONNECT_TIMEOUT => -10.5,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Timeout value should be 0.0 or greater. Supplied value: [-10.5]");
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectInvalidVerify()
    {
        $config = [
            AdapterConfigInterface::VERIFY => 1,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Verify value should be a valid boolean or a string path');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectInvalidAllowRedirects()
    {
        $config = [
            AdapterConfigInterface::ALLOW_REDIRECTS => 10,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Allow redirects value should be a valid boolean');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectIncompleteAuthConfig()
    {
        $config = [
            AdapterConfigInterface::AUTH => [
                'test',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Auth value requires at least two item in the array config');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectBlankPassword()
    {
        $config = [
            AdapterConfigInterface::AUTH => [
                'login',
                '',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password could not be a blank string');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectBlankLogin()
    {
        $config = [
            AdapterConfigInterface::AUTH => [
                '',
                'password',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Login could not be a blank string');
        GuzzleAdapterConfig::createFromArray($config);
    }

    public function testCanDetectInvalidHeaders(): void
    {
        $config = [
            AdapterConfigInterface::HEADERS => 'test'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Headers value should be a valid array');
        GuzzleAdapterConfig::createFromArray($config);
    }
}

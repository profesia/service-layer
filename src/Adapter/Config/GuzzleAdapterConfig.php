<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Profesia\ServiceLayer\ValueObject\Login;
use Profesia\ServiceLayer\ValueObject\Password;
use Profesia\ServiceLayer\ValueObject\Timeout;

/**
 * @deprecated Use AdapterConfig with GuzzleConfigTransformer instead.
 */
final class GuzzleAdapterConfig extends AbstractAdapterConfig
{
    public static function createFromArray(array $config): GuzzleAdapterConfig
    {
        if (array_key_exists(RequestOptions::TIMEOUT, $config)) {
            /** @phpstan-ignore-next-line  */
            $config[RequestOptions::TIMEOUT] = (Timeout::createFromFloat($config[RequestOptions::TIMEOUT]))->toFloat();
        }

        if (array_key_exists(RequestOptions::CONNECT_TIMEOUT, $config)) {
            /** @phpstan-ignore-next-line  */
            $config[RequestOptions::CONNECT_TIMEOUT] = (Timeout::createFromFloat($config[RequestOptions::CONNECT_TIMEOUT]))->toFloat();
        }

        if (array_key_exists(RequestOptions::VERIFY, $config)) {
            if (is_bool($config[RequestOptions::VERIFY]) === false && is_string($config[RequestOptions::VERIFY]) === false) {
                throw new InvalidArgumentException('Verify value should be a valid boolean or a string path');
            }
        }

        if (array_key_exists(RequestOptions::ALLOW_REDIRECTS, $config)) {
            if (is_bool($config[RequestOptions::ALLOW_REDIRECTS]) === false) {
                throw new InvalidArgumentException('Allow redirects value should be a valid boolean');
            }
        }

        if (array_key_exists(RequestOptions::AUTH, $config)) {
            if (is_array($config[RequestOptions::AUTH])) {
                $originalAuthConfig = $config[RequestOptions::AUTH];
                if (sizeof($originalAuthConfig) < 2) {
                    throw new InvalidArgumentException('Auth value requires at least two item in the array config');
                }

                $authConfig   = [];
                $authConfig[] = Login::createFromString($originalAuthConfig[0])->toString();
                $authConfig[] = Password::createFromString($originalAuthConfig[1])->toString();
                if (sizeof($originalAuthConfig) === 3) {
                    $authConfig[] = $originalAuthConfig[2];
                }

                $config[RequestOptions::AUTH] = $authConfig;
            }
        }

        if (array_key_exists(RequestOptions::HEADERS, $config)) {
            if (is_array($config[RequestOptions::HEADERS]) === false) {
                throw new InvalidArgumentException('Headers value should be a valid array');
            }
        }

        return new self(
            $config
        );
    }

    /**
     * @inheritDoc
     */
    public function merge(AdapterConfigInterface $config): self
    {
        $baseConfig = $this->getConfig();
        $newConfig = $config->getConfig();

        // Shallow merge - override values, not deep merge
        $mergedConfig = array_merge($baseConfig, $newConfig);

        // Deep merge for HEADERS key specifically
        if (array_key_exists(RequestOptions::HEADERS, $baseConfig) && array_key_exists(RequestOptions::HEADERS, $newConfig)) {
            /** @phpstan-ignore-next-line  */
            $mergedConfig[RequestOptions::HEADERS] = array_merge_recursive($baseConfig[RequestOptions::HEADERS], $newConfig[RequestOptions::HEADERS]);
        }

        return new self($mergedConfig);
    }
}

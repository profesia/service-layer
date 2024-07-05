<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Profesia\ServiceLayer\ValueObject\Login;
use Profesia\ServiceLayer\ValueObject\Password;
use Profesia\ServiceLayer\ValueObject\Timeout;

final class GuzzleAdapterConfig extends AbstractAdapterConfig
{
    public static function createFromArray(array $config): GuzzleAdapterConfig
    {
        $returnConfig = $config;
        if (array_key_exists(AdapterConfigInterface::TIMEOUT, $config)) {
            unset($returnConfig[AdapterConfigInterface::TIMEOUT]);
            /** @phpstan-ignore-next-line  */
            $returnConfig[RequestOptions::TIMEOUT] = (Timeout::createFromFloat($config[AdapterConfigInterface::TIMEOUT]))->toFloat();
        }

        if (array_key_exists(AdapterConfigInterface::CONNECT_TIMEOUT, $config)) {
            unset($returnConfig[AdapterConfigInterface::CONNECT_TIMEOUT]);
            /** @phpstan-ignore-next-line  */
            $returnConfig[RequestOptions::CONNECT_TIMEOUT] = (Timeout::createFromFloat($config[AdapterConfigInterface::CONNECT_TIMEOUT]))->toFloat();
        }

        if (array_key_exists(AdapterConfigInterface::VERIFY, $config)) {
            if (is_bool($config[AdapterConfigInterface::VERIFY]) === false && is_string($config[AdapterConfigInterface::VERIFY]) === false) {
                throw new InvalidArgumentException('Verify value should be a valid boolean or a string path');
            }

            unset($returnConfig[AdapterConfigInterface::VERIFY]);
            $returnConfig[RequestOptions::VERIFY] = $config[AdapterConfigInterface::VERIFY];
        }

        if (array_key_exists(AdapterConfigInterface::ALLOW_REDIRECTS, $config)) {
            if (is_bool($config[AdapterConfigInterface::ALLOW_REDIRECTS]) === false) {
                throw new InvalidArgumentException('Allow redirects value should be a valid boolean');
            }

            unset($returnConfig[AdapterConfigInterface::ALLOW_REDIRECTS]);
            $returnConfig[RequestOptions::ALLOW_REDIRECTS] = $config[AdapterConfigInterface::ALLOW_REDIRECTS];
        }

        if (array_key_exists(AdapterConfigInterface::AUTH, $config)) {
            if (is_array($config[AdapterConfigInterface::AUTH])) {
                $originalAuthConfig = $config[AdapterConfigInterface::AUTH];
                unset($returnConfig[AdapterConfigInterface::AUTH]);
                if (sizeof($originalAuthConfig) < 2) {
                    throw new InvalidArgumentException('Auth value requires at least two item in the array config');
                }

                $authConfig   = [];
                $authConfig[] = Login::createFromString($originalAuthConfig[0])->toString();
                $authConfig[] = Password::createFromString($originalAuthConfig[1])->toString();
                if (sizeof($originalAuthConfig) === 3) {
                    $authConfig[] = $originalAuthConfig[2];
                }

                $returnConfig[RequestOptions::AUTH] = $authConfig;
            }
        }

        if (array_key_exists(AdapterConfigInterface::HEADERS, $config)) {
            if (is_array($config[AdapterConfigInterface::HEADERS]) === false) {
                throw new InvalidArgumentException('Headers value should be a valid array');
            }

            $returnConfig[RequestOptions::HEADERS] = $config[AdapterConfigInterface::HEADERS];
        }

        return new self(
            $returnConfig
        );
    }
}

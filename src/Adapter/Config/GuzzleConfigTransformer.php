<?php

declare(strict_types=1);

namespace Profesia\ServiceLayer\Adapter\Config;

use GuzzleHttp\RequestOptions;
use Profesia\ServiceLayer\ValueObject\Login;
use Profesia\ServiceLayer\ValueObject\Password;
use Profesia\ServiceLayer\ValueObject\Timeout;

/**
 * Bridge class to transform platform-independent AdapterConfig to Guzzle-specific configuration.
 * Implements the Bridge design pattern to decouple platform-independent config from Guzzle specifics.
 */
final class GuzzleConfigTransformer
{
    /**
     * Transform platform-independent config to Guzzle-specific config array
     *
     * @param AdapterConfigInterface $config Platform-independent configuration
     * @return array<string, mixed> Guzzle-specific configuration array
     */
    public static function transform(AdapterConfigInterface $config): array
    {
        $sourceConfig = $config->getConfig();
        $guzzleConfig = [];

        // Transform timeout
        if (array_key_exists(AdapterConfigInterface::TIMEOUT, $sourceConfig)) {
            $guzzleConfig[RequestOptions::TIMEOUT] = Timeout::createFromFloat(
                (float)$sourceConfig[AdapterConfigInterface::TIMEOUT]
            )->toFloat();
        }

        // Transform connect_timeout
        if (array_key_exists(AdapterConfigInterface::CONNECT_TIMEOUT, $sourceConfig)) {
            $guzzleConfig[RequestOptions::CONNECT_TIMEOUT] = Timeout::createFromFloat(
                (float)$sourceConfig[AdapterConfigInterface::CONNECT_TIMEOUT]
            )->toFloat();
        }

        // Transform verify
        if (array_key_exists(AdapterConfigInterface::VERIFY, $sourceConfig)) {
            $guzzleConfig[RequestOptions::VERIFY] = $sourceConfig[AdapterConfigInterface::VERIFY];
        }

        // Transform allow_redirects
        if (array_key_exists(AdapterConfigInterface::ALLOW_REDIRECTS, $sourceConfig)) {
            $guzzleConfig[RequestOptions::ALLOW_REDIRECTS] = $sourceConfig[AdapterConfigInterface::ALLOW_REDIRECTS];
        }

        // Transform auth
        if (array_key_exists(AdapterConfigInterface::AUTH, $sourceConfig)) {
            $originalAuthConfig = $sourceConfig[AdapterConfigInterface::AUTH];
            if (is_array($originalAuthConfig) && count($originalAuthConfig) >= 2) {
                $authConfig = [];
                $authConfig[] = Login::createFromString($originalAuthConfig[0])->toString();
                $authConfig[] = Password::createFromString($originalAuthConfig[1])->toString();
                if (count($originalAuthConfig) === 3) {
                    $authConfig[] = $originalAuthConfig[2];
                }
                $guzzleConfig[RequestOptions::AUTH] = $authConfig;
            }
        }

        // Transform headers
        if (array_key_exists(AdapterConfigInterface::HEADERS, $sourceConfig)) {
            $guzzleConfig[RequestOptions::HEADERS] = $sourceConfig[AdapterConfigInterface::HEADERS];
        }

        // Pass through any other Guzzle-specific options that might already be in RequestOptions format
        foreach ($sourceConfig as $key => $value) {
            // Skip already transformed platform-independent keys
            if (in_array($key, [
                AdapterConfigInterface::TIMEOUT,
                AdapterConfigInterface::CONNECT_TIMEOUT,
                AdapterConfigInterface::VERIFY,
                AdapterConfigInterface::ALLOW_REDIRECTS,
                AdapterConfigInterface::AUTH,
                AdapterConfigInterface::HEADERS,
            ], true)) {
                continue;
            }
            
            // Pass through other options
            $guzzleConfig[$key] = $value;
        }

        return $guzzleConfig;
    }
}

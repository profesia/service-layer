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

        // Transform timeout (from Timeout value object)
        if (array_key_exists(AdapterConfigInterface::TIMEOUT, $sourceConfig)) {
            $timeout = $sourceConfig[AdapterConfigInterface::TIMEOUT];
            if ($timeout instanceof Timeout) {
                $guzzleConfig[RequestOptions::TIMEOUT] = $timeout->toFloat();
            } else {
                // Fallback for backward compatibility
                $guzzleConfig[RequestOptions::TIMEOUT] = Timeout::createFromFloat((float)$timeout)->toFloat();
            }
        }

        // Transform connect_timeout (from Timeout value object)
        if (array_key_exists(AdapterConfigInterface::CONNECT_TIMEOUT, $sourceConfig)) {
            $connectTimeout = $sourceConfig[AdapterConfigInterface::CONNECT_TIMEOUT];
            if ($connectTimeout instanceof Timeout) {
                $guzzleConfig[RequestOptions::CONNECT_TIMEOUT] = $connectTimeout->toFloat();
            } else {
                // Fallback for backward compatibility
                $guzzleConfig[RequestOptions::CONNECT_TIMEOUT] = Timeout::createFromFloat((float)$connectTimeout)->toFloat();
            }
        }

        // Transform verify (already primitive type)
        if (array_key_exists(AdapterConfigInterface::VERIFY, $sourceConfig)) {
            $guzzleConfig[RequestOptions::VERIFY] = $sourceConfig[AdapterConfigInterface::VERIFY];
        }

        // Transform allow_redirects (already primitive type)
        if (array_key_exists(AdapterConfigInterface::ALLOW_REDIRECTS, $sourceConfig)) {
            $guzzleConfig[RequestOptions::ALLOW_REDIRECTS] = $sourceConfig[AdapterConfigInterface::ALLOW_REDIRECTS];
        }

        // Transform auth (from Login/Password value objects)
        if (array_key_exists(AdapterConfigInterface::AUTH, $sourceConfig)) {
            $originalAuthConfig = $sourceConfig[AdapterConfigInterface::AUTH];
            if (is_array($originalAuthConfig) && count($originalAuthConfig) >= 2) {
                $authConfig = [];
                
                // Handle Login value object
                if ($originalAuthConfig[0] instanceof Login) {
                    $authConfig[] = $originalAuthConfig[0]->toString();
                } else {
                    // Fallback for backward compatibility
                    $authConfig[] = Login::createFromString($originalAuthConfig[0])->toString();
                }
                
                // Handle Password value object
                if ($originalAuthConfig[1] instanceof Password) {
                    $authConfig[] = $originalAuthConfig[1]->toString();
                } else {
                    // Fallback for backward compatibility
                    $authConfig[] = Password::createFromString($originalAuthConfig[1])->toString();
                }
                
                // Optional third parameter (e.g., 'digest')
                if (count($originalAuthConfig) >= 3) {
                    $authConfig[] = $originalAuthConfig[2];
                }
                
                $guzzleConfig[RequestOptions::AUTH] = $authConfig;
            }
        }

        // Transform headers (already array)
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

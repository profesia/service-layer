---
layout: default
title: Service Layer
---

# Service Layer

Library designed for SOA communication via REST with external services.

[![Build and test](https://github.com/profesia/service-layer/actions/workflows/test-runner.yml/badge.svg?branch=master)](https://github.com/profesia/service-layer/actions/workflows/test-runner.yml)
![PHP Version](https://img.shields.io/packagist/php-v/profesia/service-layer)
![License](https://img.shields.io/github/license/profesia/service-layer)

## Overview

Service Layer is a PHP library that provides a clean abstraction layer for communicating with external REST services. It follows the Service-Oriented Architecture (SOA) pattern and implements PSR standards for HTTP messaging.

## Key Features

- **PSR-7 Compliant**: Full support for PSR-7 HTTP message interfaces
- **PSR-18 HTTP Client**: Compatible with any PSR-18 HTTP client
- **Guzzle Integration**: Built-in adapter for Guzzle HTTP client
- **Request Logging**: Comprehensive logging of requests and responses
- **Response Mapping**: Custom domain response mapping
- **Caching Proxy**: Built-in caching support for responses
- **Gateway Use Case Registry**: Organize and manage multiple service endpoints

## Quick Links

- [Getting Started](getting-started) - Installation and basic setup
- [Architecture](architecture) - Understanding the library components
- [Usage Guide](usage-guide) - Detailed usage examples

## Installation

Install the latest version via Composer:

```bash
composer require profesia/service-layer
```

## Requirements

- PHP 8.0 or higher

## Basic Example

```php
use GuzzleHttp\Client;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use Profesia\ServiceLayer\Transport\Gateway;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Psr\Log\NullLogger;

// Create the adapter
$guzzleClient = new Client(['base_uri' => 'https://api.example.com']);
$adapterConfig = new GuzzleAdapterConfig();
$adapter = new GuzzleAdapter($guzzleClient, $adapterConfig);

// Create the logger
$logger = new CommunicationLogger(new NullLogger());

// Create the gateway
$gateway = new Gateway($adapter, $logger);

// Send a request
$response = $gateway->sendRequest($yourRequest);
```

## Author

Matej Bádal - matej.badal@almacareer.com

Alma Career Slovakia s.r.o.

## License

This project is licensed under the MIT License.

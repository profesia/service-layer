---
layout: default
title: Getting Started
---

# Getting Started

This guide will help you get started with the Service Layer library.

## Installation

Install the package via Composer:

```bash
composer require profesia/service-layer
```

## Requirements

- PHP 8.0 or higher
- Guzzle HTTP client 7.0 or higher (for the built-in adapter)

## Dependencies

The library depends on the following PSR interfaces:

- `psr/log` - Logging interface
- `psr/http-message` - HTTP message interfaces
- `psr/http-client` - HTTP client interface
- `psr/http-factory` - HTTP factory interfaces
- `psr/simple-cache` - Caching interface

## Basic Setup

### 1. Create an Adapter

The adapter is responsible for sending HTTP requests. The library comes with a built-in Guzzle adapter:

```php
use GuzzleHttp\Client;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;

$guzzleClient = new Client([
    'base_uri' => 'https://api.example.com',
    'timeout' => 30,
]);

$adapterConfig = new GuzzleAdapterConfig();
$adapter = new GuzzleAdapter($guzzleClient, $adapterConfig);
```

### 2. Create a Logger

The gateway requires a logger to track requests and responses:

```php
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Psr\Log\NullLogger;

// Using PSR-3 NullLogger for no logging
$logger = new CommunicationLogger(new NullLogger());

// Or with a real logger (e.g., Monolog)
// $logger = new CommunicationLogger($monologLogger);
```

### 3. Create the Gateway

The gateway is the main entry point for sending requests:

```php
use Profesia\ServiceLayer\Transport\Gateway;

$gateway = new Gateway($adapter, $logger);
```

### 4. Create a Request

Extend the `AbstractGatewayRequest` class to create your request:

```php
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;

class GetUserRequest extends AbstractGatewayRequest
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        parent::__construct(new Psr17Factory());
    }

    protected function getMethod(): HttpMethod
    {
        return HttpMethod::createGet();
    }

    protected function getUri(): UriInterface
    {
        return new Uri("/users/{$this->userId}");
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => ['application/json'],
            'Content-Type' => ['application/json'],
        ];
    }

    protected function getBody(): ?StreamInterface
    {
        return null;
    }
}
```

### 5. Send the Request

```php
$request = new GetUserRequest(123);
$response = $gateway->sendRequest($request);

if ($response->isSuccessful()) {
    $body = $response->getResponseBody();
    // Process the response body
}
```

## Next Steps

- Learn about the [Architecture](architecture) of the library
- Explore advanced [Usage Guide](usage-guide) examples

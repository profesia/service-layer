# Service Layer
 
Library designed for SOA communication via REST with external services

[![Build and test](https://github.com/profesia/service-layer/actions/workflows/test-runner.yml/badge.svg?branch=master)](https://github.com/profesia/psr15-symfony-bundle/actions/workflows/test-runner.yml)
![PHP Version](https://img.shields.io/packagist/php-v/profesia/service-layer)
![License](https://img.shields.io/github/license/profesia/service-layer)

## Installation
Install the latest version by running the command
```bash
composer require profesia/service-layer
```
## Requirements
- PHP 8.0+

## Quick Start

For quick prototyping and testing of API endpoints, you can use the `ServiceLayer` facade class:

```php
use Profesia\ServiceLayer\Facade\ServiceLayer;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Transport\Gateway;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use GuzzleHttp\Client;
use Psr\Log\NullLogger;

// Set up dependencies
$client = new Client();
$adapter = new GuzzleAdapter($client, AdapterConfig::createDefault());
$logger = new CommunicationLogger(new NullLogger());
$gateway = new Gateway($adapter, $logger);
$requestFactory = new Psr17Factory();

// Create a facade instance
$facade = new ServiceLayer($gateway, $requestFactory);

// Make a simple GET request
$uri = new Uri('https://api.example.com/users');
$response = $facade->executeRequest($uri, HttpMethod::createGet());

// Make a POST request with a body
$uri = new Uri('https://api.example.com/users');
$body = Stream::create(json_encode(['name' => 'John Doe']));
$response = $facade->executeRequest($uri, HttpMethod::createPost(), $body);

// Make a request with custom client options (timeout, SSL verification, etc.)
$clientOptions = ['timeout' => 10.0, 'verify' => false];
$response = $facade->executeRequest($uri, HttpMethod::createGet(), null, $clientOptions);

// Use builder pattern with custom response mapper
$response = $facade
    ->withMapper(function ($endpointResponse) {
        // Custom response transformation logic
        return MyCustomResponse::fromEndpoint($endpointResponse);
    })
    ->executeRequest($uri, HttpMethod::createGet());

// Use builder pattern with client options (state is reset after each request)
$response = $facade
    ->withClientOptions(['timeout' => 10.0, 'verify' => false])
    ->executeRequest($uri, HttpMethod::createGet());

// Check the response
if ($response->isSuccessful()) {
    echo $response->getResponseBody();
}
```

See the [examples](examples/service-layer-usage.php) directory for more usage examples.

## Documentation
The documentation for the bundle can be found at https://profesia.github.io/service-layer

## Author
Matej Bádal - matej.badal@almacareer.com

Alma Career Slovakia s r.o.
## License
This project is licensed under the MIT License

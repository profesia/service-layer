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

For quick prototyping and testing of API endpoints, you can use the `SimpleFacade` class:

```php
use Profesia\ServiceLayer\Transport\SimpleFacade;
use Nyholm\Psr7\Stream;

// Create a facade instance
$facade = new SimpleFacade();

// Make a simple GET request
$response = $facade->executeRequest('https://api.example.com/users', 'GET');

// Make a POST request with a body
$body = Stream::create(json_encode(['name' => 'John Doe']));
$response = $facade->executeRequest('https://api.example.com/users', 'POST', $body);

// Check the response
if ($response->isSuccessful()) {
    echo $response->getResponseBody()->getContents();
}
```

See the [examples](examples/simple-facade-usage.php) directory for more usage examples.

## Documentation
The documentation for the bundle can be found at https://profesia.github.io/service-layer

## Author
Matej Bádal - matej.badal@almacareer.com

Alma Career Slovakia s r.o.
## License
This project is licensed under the MIT License

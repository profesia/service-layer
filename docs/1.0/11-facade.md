`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Facade](11-facade.html)

# Facade

## Why we created the ServiceLayer facade

The ServiceLayer library provides a powerful and flexible architecture for integrating with REST APIs in a service-oriented manner. While this architecture excels at production scenarios with complex requirements, it can sometimes feel verbose for simple prototyping, testing, or quick API endpoint integrations.

### The Problem

When you just need to quickly test an API endpoint or prototype a simple integration, setting up the full architecture (Gateway, Adapter, Request classes, Mappers, etc.) can feel like overkill. For example, to make a simple GET request, you would typically need to:

1. Create a dedicated Request class extending `AbstractGatewayRequest`
2. Set up an Adapter with configuration
3. Configure a Gateway
4. Optionally create a Mapper
5. Wire everything together

This is excellent for production code where you need reusability, encapsulation, and proper separation of concerns. However, for quick testing or prototyping, it adds unnecessary complexity.

### The Solution: ServiceLayer Facade

The `ServiceLayer` facade class provides a simplified, streamlined interface that wraps the underlying architecture while maintaining type safety and leveraging all the powerful features of the library. It's designed specifically for:

- **Rapid prototyping** - Quickly test API endpoints without creating multiple classes
- **Unit/integration testing** - Simplify test setup for API communication
- **Simple one-off integrations** - When you don't need the full power of the architecture
- **Learning the library** - Easier entry point for understanding how components work together

### Key Benefits

1. **Minimal Setup** - Just create a facade instance with Gateway and RequestFactory dependencies
2. **Type Safety** - Uses value objects (HttpMethod, UriInterface) for strict typing
3. **Fluent Builder Pattern** - Chain method calls for clean, readable code
4. **Automatic State Reset** - Mapper state resets after each request for predictable behavior
5. **Platform-Independent Configuration** - Uses the new `AdapterConfig` for configuration
6. **Flexible Response Mapping** - Support for closure-based mappers via `ClosureMapper`

## Basic Usage

### Setup

First, set up the required dependencies (this is the only "heavy" part):

```php
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Facade\ServiceLayer;
use Profesia\ServiceLayer\Transport\Gateway;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Psr\Log\NullLogger;

// Set up dependencies (typically done once in your application bootstrap)
$client = new Client();
$adapter = new GuzzleAdapter($client, AdapterConfig::createDefault());
$logger = new CommunicationLogger(new NullLogger());
$gateway = new Gateway($adapter, $logger);
$requestFactory = new Psr17Factory();

// Create the facade instance
$facade = new ServiceLayer($gateway, $requestFactory);
```

### Simple GET Request

```php
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users');
$response = $facade->sendRequest($uri, HttpMethod::createGet());

if ($response->isSuccessful()) {
    echo $response->getResponseBody();
}
```

### POST Request with Body

```php
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users');
$body = Stream::create(json_encode(['name' => 'John Doe', 'email' => 'john@example.com']));

$response = $facade->sendRequest($uri, HttpMethod::createPost(), $body);
```

### PUT Request

```php
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users/123');
$body = Stream::create(json_encode(['status' => 'active']));

$response = $facade->sendRequest($uri, HttpMethod::createPut(), $body);
```

### DELETE Request

```php
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users/123');
$response = $facade->sendRequest($uri, HttpMethod::createDelete());
```

## Advanced Usage

### Custom Client Configuration

You can override adapter configuration per request using the fourth parameter:

```php
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users');

// Create custom config with specific timeout and SSL verification
$config = AdapterConfig::createFromArray([
    'timeout' => 10.0,
    'connect_timeout' => 5.0,
    'verify' => false,
    'headers' => [
        'X-Custom-Header' => 'value',
    ],
]);

$response = $facade->sendRequest($uri, HttpMethod::createGet(), null, $config);
```

### Builder Pattern with Custom Mapper

The facade supports a fluent builder pattern for setting up custom response mappers:

```php
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users');

$response = $facade
    ->withMapperClosure(function ($endpointResponse) {
        // Custom transformation logic
        $data = json_decode($endpointResponse->getResponseBody()->__toString(), true);
        
        // Transform to your domain object
        return MyCustomResponse::fromArray($data);
    })
    ->sendRequest($uri, HttpMethod::createGet());
```

### Using a Dedicated Mapper

If you already have a mapper class implementing `ResponseDomainMapperInterface`:

```php
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users');
$mapper = new MyCustomMapper();

$response = $facade
    ->withResponseMapper($mapper)
    ->sendRequest($uri, HttpMethod::createGet());
```

### Automatic State Reset

The facade automatically resets the mapper state after each request, ensuring predictable behavior:

```php
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

$uri = new Uri('https://api.example.com/users');

// First request with custom mapper
$response1 = $facade
    ->withMapperClosure(function ($endpointResponse) {
        return CustomResponse::fromEndpoint($endpointResponse);
    })
    ->sendRequest($uri, HttpMethod::createGet());

// Second request - mapper is automatically reset to null
// Uses the default mapper behavior
$response2 = $facade->sendRequest($uri, HttpMethod::createGet());
```

## When to Use the Facade

### ✅ Good Use Cases

- **Prototyping** - Quickly test API endpoints during development
- **Testing** - Simplify test setup for integration tests
- **Simple scripts** - One-off data migration or admin scripts
- **Learning** - Understanding how the library works
- **POC/MVP** - Proof of concept implementations

### ❌ When to Use the Full Architecture

- **Production applications** - When you need proper separation of concerns
- **Complex integrations** - Multiple endpoints with different configurations
- **Reusable services** - When the same endpoint is used in multiple places
- **Team projects** - Where explicit contracts and testability are important
- **Advanced features** - Custom headers per request, authentication, retry logic, etc.

## Architecture Notes

The `ServiceLayer` facade internally uses:

- **SimpleRequest** - A lightweight request implementation
- **ClosureMapper** - For closure-based response mapping
- **AdapterConfig** - Platform-independent configuration
- **Gateway** - The core communication component

It doesn't replace the architecture; it simply provides a convenience layer on top of it. You can always switch to the full architecture when your needs grow.

## Configuration Merging

When working with configurations, you can leverage the `merge()` method for composition:

```php
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;

// Base configuration
$baseConfig = AdapterConfig::createFromArray([
    'timeout' => 10.0,
    'headers' => [
        'X-Custom-Header' => 'value1',
        'X-Auth' => 'token1',
    ],
]);

// Override configuration
$overrides = AdapterConfig::createFromArray([
    'timeout' => 20.0, // Overrides timeout
    'headers' => [
        'X-Auth' => 'token2', // Overrides auth token
        'X-New-Header' => 'value2', // Adds new header
    ],
]);

// Merge configs (shallow for most keys, deep for headers)
$merged = $baseConfig->merge($overrides);

// Result:
// - timeout: 20.0 (overridden)
// - headers: {
//     'X-Custom-Header': 'value1',  // Preserved from base
//     'X-Auth': 'token2',            // Overridden
//     'X-New-Header': 'value2'       // Added
//   }
```

## Summary

The ServiceLayer facade strikes a balance between simplicity and power. It provides:

- A simple 4-parameter interface (URI, method, body, config)
- Type safety through value objects
- Fluent builder pattern for advanced scenarios
- Automatic state management
- Platform-independent configuration
- All the power of the underlying architecture when you need it

For quick prototyping and testing, it's the perfect entry point. For production applications with complex requirements, consider using the full architecture with dedicated Request, Mapper, and UseCase classes.

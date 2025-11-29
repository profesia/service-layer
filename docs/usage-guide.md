---
layout: default
title: Usage Guide
---

# Usage Guide

This guide provides detailed examples of using the Service Layer library.

## Creating Custom Requests

### Basic Request

Create a simple GET request by extending `AbstractGatewayRequest`:

```php
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;

class GetUsersRequest extends AbstractGatewayRequest
{
    public function __construct()
    {
        parent::__construct(new Psr17Factory());
    }

    protected function getMethod(): HttpMethod
    {
        return HttpMethod::createGet();
    }

    protected function getUri(): UriInterface
    {
        return new Uri('/api/users');
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => ['application/json'],
        ];
    }

    protected function getBody(): ?StreamInterface
    {
        return null;
    }
}
```

### POST Request with Body

```php
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Uri;

class CreateUserRequest extends AbstractGatewayRequest
{
    private array $userData;
    private Psr17Factory $factory;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
        $this->factory = new Psr17Factory();
        parent::__construct($this->factory);
    }

    protected function getMethod(): HttpMethod
    {
        return HttpMethod::createPost();
    }

    protected function getUri(): UriInterface
    {
        return new Uri('/api/users');
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
        return $this->factory->createStream(
            json_encode($this->userData)
        );
    }
}
```

### Request with Sensitive Data Censoring

Override the censoring methods to hide sensitive data in logs:

```php
class AuthenticatedRequest extends AbstractGatewayRequest
{
    private string $apiKey;
    
    // ... constructor and other methods

    protected function getHeaders(): array
    {
        return [
            'Authorization' => ["Bearer {$this->apiKey}"],
            'Content-Type' => ['application/json'],
        ];
    }

    public function getCensoredHeaders(): array
    {
        return [
            'Authorization' => ['Bearer ***REDACTED***'],
            'Content-Type' => ['application/json'],
        ];
    }
}
```

## Configuring Adapters

### Basic Guzzle Configuration

```php
use GuzzleHttp\Client;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;

$guzzleClient = new Client([
    'base_uri' => 'https://api.example.com',
]);

$adapterConfig = GuzzleAdapterConfig::createFromArray([
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$adapter = new GuzzleAdapter($guzzleClient, $adapterConfig);
```

### Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `timeout` | float | Request timeout in seconds |
| `connect_timeout` | float | Connection timeout in seconds |
| `verify` | bool/string | SSL verification (true, false, or CA bundle path) |
| `allow_redirects` | bool | Whether to follow redirects |
| `auth` | array | Authentication credentials `[username, password, type]` |
| `headers` | array | Default headers to include |

### With Authentication

```php
$adapterConfig = GuzzleAdapterConfig::createFromArray([
    'timeout' => 30,
    'auth' => ['username', 'password', 'basic'],
]);
```

## Response Mapping

### Custom Domain Mapper

Create a mapper to transform responses into domain objects:

```php
use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;

class UserResponseMapper implements ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response): DomainResponseInterface
    {
        $body = json_decode((string)$response->getBody(), true);
        
        return new UserResponse(
            $response->getStatusCode(),
            new User(
                $body['id'],
                $body['name'],
                $body['email']
            )
        );
    }
}
```

### Using the Mapper

```php
$response = $gateway->sendRequest($request, new UserResponseMapper());
```

## Caching Responses

### Basic Caching Setup

```php
use Profesia\ServiceLayer\Transport\Proxy\GatewayCachingProxy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Create PSR-16 cache
$cache = new Psr16Cache(new FilesystemAdapter());

// Create caching proxy
$cachingGateway = new GatewayCachingProxy($cache, $gateway);

// Use like a normal gateway
$response = $cachingGateway->sendRequest($request);
```

### Custom Cache Configuration

Implement `CacheConfigInterface` for custom caching behavior:

```php
use Profesia\ServiceLayer\Transport\Proxy\Config\CacheConfigInterface;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Psr\Http\Message\RequestInterface;

class CustomCacheConfig implements CacheConfigInterface
{
    public function getCacheKeyForRequest(RequestInterface $request): string
    {
        // Custom cache key generation
        return 'my_prefix_' . md5($request->getUri() . $request->getMethod());
    }

    public function shouldBeResponseForRequestBeCached(
        RequestInterface $request,
        DomainResponseInterface $response
    ): bool {
        // Only cache GET requests that are successful
        return $request->getMethod() === 'GET' && $response->isSuccessful();
    }

    public function getTtlForRequest(RequestInterface $request): ?int
    {
        // Cache for 1 hour
        return 3600;
    }
}

$cachingGateway = new GatewayCachingProxy($cache, $gateway, new CustomCacheConfig());
```

## Using the Registry

### Setup Use Case Registry

```php
use Profesia\ServiceLayer\Registry\GatewayUseCaseRegistry;

$registry = GatewayUseCaseRegistry::createFromArrayConfig([
    'defaultGateway' => $gateway,
    'requests' => [
        'getUsers' => [
            'request' => new GetUsersRequest(),
            'mapper' => new UserListMapper(),
        ],
        'getUser' => [
            'request' => null, // Will be set dynamically
            'mapper' => new UserResponseMapper(),
        ],
        'createUser' => [
            'request' => null,
            'mapper' => new UserResponseMapper(),
            'configOverride' => GuzzleAdapterConfig::createFromArray([
                'timeout' => 60,
            ]),
        ],
    ],
]);
```

### Execute Use Cases

```php
// Execute with pre-configured request
$response = $registry->processUseCase('getUsers');

// Get use case and set request dynamically
$useCase = $registry->getConfiguredGatewayUseCase('getUser');
$response = $useCase
    ->setRequestToSend(new GetUserRequest(123))
    ->performRequest();
```

### Override Configuration

```php
$useCase = $registry->getConfiguredGatewayUseCase('createUser');
$response = $useCase
    ->setRequestToSend(new CreateUserRequest($userData))
    ->viaAdapter($customAdapter)
    ->useLogger($customLogger)
    ->withMapper(new CustomMapper())
    ->performRequest();
```

## Logging

### Custom Logger Implementation

```php
use Profesia\ServiceLayer\Transport\Logging\GatewayLoggerInterface;
use Profesia\ServiceLayer\Request\GatewayRequestInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface;
use DateTimeImmutable;
use Exception;

class CustomLogger implements GatewayLoggerInterface
{
    public function logRequestResponsePair(
        GatewayRequestInterface $request,
        EndpointResponseInterface $response,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        $elapsed = $stop->getTimestamp() - $start->getTimestamp();
        
        // Log to your preferred system
        error_log(sprintf(
            '[%s] %s %s - %d (%ds)',
            $logLevel,
            $request->toPsrRequest()->getMethod(),
            $request->getCensoredUri(),
            $response->getStatusCode()->toInt(),
            $elapsed
        ));
    }

    public function logRequestExceptionPair(
        GatewayRequestInterface $request,
        Exception $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void {
        error_log(sprintf(
            '[%s] %s %s - Exception: %s',
            $logLevel,
            $request->toPsrRequest()->getMethod(),
            $request->getCensoredUri(),
            $exception->getMessage()
        ));
    }
}
```

### Using PSR-3 Logger

```php
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$monolog = new Logger('api');
$monolog->pushHandler(new StreamHandler('path/to/api.log'));

$logger = new CommunicationLogger($monolog);
$gateway = new Gateway($adapter, $logger);
```

## Error Handling

### Handling Domain Errors

```php
$response = $gateway->sendRequest($request);

if ($response->isSuccessful()) {
    $data = $response->getResponseBody();
    // Process successful response
} else {
    // Handle error
    if ($response instanceof ErrorResponse) {
        $exception = $response->getException();
        // Log or handle the exception
    }
}
```

### Adapter Exceptions

```php
use Profesia\ServiceLayer\Adapter\Exception\AdapterException;

try {
    $response = $gateway->sendRequest($request);
} catch (AdapterException $e) {
    // Handle connection/network errors
    echo "Connection failed: " . $e->getMessage();
}
```

## Best Practices

### 1. Always Use Value Objects

Use the provided value objects for type safety:

```php
// Good
$method = HttpMethod::createGet();
$timeout = Timeout::createFromFloat(30.0);

// Avoid
$method = 'GET';
$timeout = 30;
```

### 2. Implement Proper Censoring

Always override censoring methods for sensitive data:

```php
public function getCensoredHeaders(): array
{
    $headers = $this->getHeaders();
    if (isset($headers['Authorization'])) {
        $headers['Authorization'] = ['***REDACTED***'];
    }
    return $headers;
}
```

### 3. Use Dependency Injection

Inject dependencies rather than creating them inline:

```php
// Good
class MyService
{
    private GatewayInterface $gateway;
    
    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}

// Avoid
class MyService
{
    public function doSomething()
    {
        $gateway = new Gateway(new GuzzleAdapter(...), ...);
    }
}
```

### 4. Create Domain-Specific Mappers

Map responses to domain objects for cleaner code:

```php
// Good
$response = $gateway->sendRequest($request, new UserMapper());
$user = $response->getUser();

// Avoid
$response = $gateway->sendRequest($request);
$body = json_decode($response->getResponseBody(), true);
$user = new User($body['id'], $body['name']);
```

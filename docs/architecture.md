---
layout: default
title: Architecture
---

# Architecture

This page describes the architecture and components of the Service Layer library.

## Component Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              Service Layer                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────────────────┐  │
│  │   Request     │────▶│   Gateway     │────▶│       Adapter             │  │
│  │   (PSR-7)     │     │               │     │    (Guzzle/PSR-18)        │  │
│  └───────────────┘     └───────────────┘     └───────────────────────────┘  │
│         │                     │                          │                  │
│         │                     ▼                          │                  │
│         │              ┌───────────────┐                 │                  │
│         │              │    Logger     │                 │                  │
│         │              │   (PSR-3)     │                 │                  │
│         │              └───────────────┘                 │                  │
│         │                                                │                  │
│         │                                                ▼                  │
│         │                                       ┌───────────────┐           │
│         │                                       │   Response    │           │
│         │                                       │  (Connection) │           │
│         │                                       └───────────────┘           │
│         │                                                │                  │
│         ▼                                                ▼                  │
│  ┌───────────────┐                              ┌───────────────┐           │
│  │    Mapper     │◀─────────────────────────────│   Response    │           │
│  │   (Domain)    │                              │   (Domain)    │           │
│  └───────────────┘                              └───────────────┘           │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Core Components

### Request

The request layer handles the creation and configuration of HTTP requests.

#### GatewayRequestInterface

The main interface for creating gateway requests:

```php
interface GatewayRequestInterface
{
    public function toPsrRequest(): RequestInterface;
    public function getCensoredBody(): ?StreamInterface;
    public function getCensoredHeaders(): array;
    public function getCensoredUri(): UriInterface;
}
```

#### AbstractGatewayRequest

Base class for implementing custom requests:

```php
abstract class AbstractGatewayRequest implements GatewayRequestInterface
{
    abstract protected function getMethod(): HttpMethod;
    abstract protected function getUri(): UriInterface;
    abstract protected function getHeaders(): array;
    abstract protected function getBody(): ?StreamInterface;
}
```

### Transport

The transport layer is responsible for sending requests and receiving responses.

#### GatewayInterface

```php
interface GatewayInterface
{
    public function viaAdapter(AdapterInterface $adapter): self;
    public function useLogger(GatewayLoggerInterface $logger): self;
    public function sendRequest(
        GatewayRequestInterface $gatewayRequest,
        ?ResponseDomainMapperInterface $mapper = null,
        ?AdapterConfigInterface $adapterOverrideConfigBuilder = null
    ): DomainResponseInterface;
}
```

#### Gateway

The main implementation of `GatewayInterface`. It:

- Sends requests through an adapter
- Logs request/response pairs
- Maps responses to domain objects
- Handles exceptions

### Adapter

Adapters are responsible for the actual HTTP communication.

#### AdapterInterface

```php
interface AdapterInterface
{
    public function send(
        GatewayRequestInterface $request,
        ?AdapterConfigInterface $configOverrideBuilder = null
    ): EndpointResponseInterface;
}
```

#### GuzzleAdapter

Built-in adapter for the Guzzle HTTP client:

- Handles request configuration
- Manages headers merging
- Converts exceptions to adapter exceptions

### Response

The response layer has two levels:

#### Connection Response

Low-level HTTP response wrapper (`EndpointResponseInterface`):

- Status code
- Headers
- Body

#### Domain Response

High-level domain response (`DomainResponseInterface`):

- Business logic aware
- Success/failure status
- Response body

### Mapper

The mapper transforms connection responses to domain responses:

```php
interface ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response): DomainResponseInterface;
}
```

### Logging

The logging component tracks all communication.

#### GatewayLoggerInterface

```php
interface GatewayLoggerInterface
{
    public function logRequestResponsePair(
        GatewayRequestInterface $request,
        EndpointResponseInterface $response,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void;

    public function logRequestExceptionPair(
        GatewayRequestInterface $request,
        Exception $exception,
        DateTimeImmutable $start,
        DateTimeImmutable $stop,
        string $logLevel
    ): void;
}
```

#### CommunicationLogger

Default implementation that:

- Logs request method and URI
- Includes request/response headers
- Includes request/response bodies
- Calculates elapsed time

### Registry

The registry provides a way to organize and manage multiple service endpoints.

#### GatewayUseCase

A fluent interface for configuring and executing requests:

```php
$useCase = new GatewayUseCase($gateway, $request, $mapper);
$response = $useCase
    ->viaAdapter($customAdapter)
    ->useLogger($customLogger)
    ->performRequest();
```

### Proxy

Proxies add additional functionality to gateways.

#### GatewayCachingProxy

Adds caching to gateway requests:

- Checks cache before sending request
- Caches successful responses
- Configurable TTL and cache key generation

## Value Objects

### HttpMethod

Represents HTTP methods with named constructors:

```php
HttpMethod::createGet();
HttpMethod::createPost();
HttpMethod::createPut();
HttpMethod::createDelete();
HttpMethod::createFromString('PATCH');
```

### StatusCode

Represents HTTP status codes with helper methods:

```php
$statusCode->isSuccess();  // 2xx
$statusCode->toString();
```

### Timeout

Represents request timeout configuration.

### Login / Password

Value objects for authentication credentials.

## PSR Compliance

The library implements the following PSR standards:

| PSR | Description | Usage |
|-----|-------------|-------|
| PSR-3 | Logger Interface | Gateway logging |
| PSR-7 | HTTP Message Interface | Request/Response objects |
| PSR-17 | HTTP Factories | Request creation |
| PSR-18 | HTTP Client | Adapter interface |
| PSR-16 | Simple Cache | Caching proxy |

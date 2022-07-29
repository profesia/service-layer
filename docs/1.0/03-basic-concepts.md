`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Basic concepts](03-basic-concepts.html)

# Basic concepts

Having in mind definition of a SOA and also good practices of a software
development we have designed Service layer around a few standard principles:

* Reusability
* Encapsulation
* Easy configuration
* Extensibility
* Separation of concerns

## Logical components

* [GatewayRequestInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Request/GatewayRequestInterface.php)
* [AdapterInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Adapter/AdapterInterface.php)
* [GatewayInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/GatewayInterface.php)
* [ResponseDomainMapperInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Mapper/ResponseDomainMapperInterface.php)
* [LoggerInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/src/Transport/Logging/RequestGatewayLoggerInterface.php)

## Core ideas

* [Request](https://github.com/profesia/service-layer/blob/v0.9.0/src/Request/GatewayRequestInterface.php) contract represents basic contract
  for wrapping mandatory parameters for service execution - endpoint URL, headers, request body and HTTP method.
  Reusability is ensured by name of a request - because it contains all necessary information and
  is properly named, it can be initialized and reused in any place.
* [Response](https://github.com/profesia/service-layer/blob/v0.9.0/src/Response/GatewayResponseInterface.php) minimalistic contract represents any response
  that can be emitted from adapter context. Because there are many differences in between
  connection response and domain response, we have defined two separate contracts for each case:
    * [EndpointResponse](https://github.com/profesia/service-layer/blob/v0.9.0/src/Response/Connection/EndpointResponseInterface.php) represents connection response
      context agnostic response, that only knows whether the request was successful (by reading HTTP status code)
      and has access to the response body.
    * [DomainResponseInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Response/Domain/GatewayDomainResponseInterface.php) represents domain response
      wrapper
      that is able to retrieve specific domain object.
* [Adapter](https://github.com/profesia/service-layer/blob/v0.9.0/src/Adapter/AdapterInterface.php) contract represents wrapper for any adapter capable of sending of a
  HTTP requests.
* [Mapper](https://github.com/profesia/service-layer/blob/v0.9.0/src/Mapper/ResponseDomainMapperInterface.php) contract represents a component
  capable of mapping [EndpointResponse](https://github.com/profesia/service-layer/blob/v0.9.0/src/Response/Connection/EndpointResponseInterface.php)
  onto [DomainResponseInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Response/Domain/GatewayDomainResponseInterface.php)
* [GatewayUseCase](https://github.com/profesia/service-layer/blob/v0.9.0/src/Registry/GatewayUseCase.php) contract represents one specific use
  case - request with all the necessary configuration and means to override its one time configuration.
* [GatewayUseCaseRegistry](https://github.com/profesia/service-layer/blob/v0.9.0/src/Registry/GatewayUseCaseRegistry.php) contract represents registry of gateway use
  cases
  and predefines required structure for the necessary configuration with the capability to override each part of a
  communication.

## Model

In the following extended model the core list of classes is shown along with their relations:
![Overview UML class diagram](../assets/img/service-layer-overview.svg)

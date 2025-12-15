`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Logger](08-logger.html)

# Logger

As its name suggest, the logger contract represents the means of monitoring the communication with
a remote URL.
The logger contract is represented via
interface [GatewayLoggerInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/Logging/GatewayLoggerInterface.php).
The contract is consistent of two methods with different use cases:

* [logRequestResponsePair](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/Logging/GatewayLoggerInterface.php).
* [logRequestException](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/Logging/GatewayLoggerInterface.php).

The contract itself does not enforce any "standard" of resources those are being logged or how are those being logged.
It is pretty simple and straightforward and only distinguishes between two scenarios:

* **Successful communication** - request and response pair.
* **Error** - request and exception pair.

## Default Logger

The library comes with default logger
implementation [CommunicationGatewayLogger](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/Logging/CommunicationGatewayLogger.php),
that serves as a "standard" remote communication logger - logs all the communication details:

* Request and response headers.
* Request body.
* Response status code.
* Communication elapsed time.
* Response or exception body.

## Censoring Sensitive Data

As stated in the [Request section](04-request.html#censoring-critical-data), tho logger respects data sensitivy
and is using **censored** method family for communication logging.

## Additional Functionality

In general, logging of all communications parts is and important part of the monitoring process.
But there are scenarios, when it is redundant, or even harmful - for example logging of binary content.
This kind of content has no informative value to the end user and it may cause logs data overflow, when it comes
to the document API.
By offering of the base contract [GatewayLoggerInterface](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/Logging/GatewayLoggerInterface.php)
we have enabled the end user to use multiple structural design patterns and alter/expand the base functionality of
the default logger.
As a simple solution a decorator pattern was used. Currently implemented list of decorators:

* [ResponseBodyTrimmingDecorator](https://github.com/profesia/service-layer/blob/v0.9.0/src/Transport/Logging/Decorator/ResponseBodyTrimmingDecorator.php)
  Trims response body - replaces response body (on successful requests) with predefined string.


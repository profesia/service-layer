`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Client Adapter](06-client-adapter.html)

# Client Adapter

Client adapter represents contract capable of
transforming [GatewayRequest](../../src/Request/GatewayRequestInterface.php)
into [GatewayResponse](../../src/Response/GatewayResponseInterface.php) via HTTP client.
Adapter should be able to handle all the possible situations able to occur during transport of a request:

* Communication with remote URL.
* Handling of a response from URL.
* Handling of any error, that could occur during communication.
* Client configuration override

## Architecture

We did not want to have library dependant on the specific HTTP client. Although the only implementation in the
library is based on Guzzle, we wanted for the end user to have freed of choice when it comes to the HTTP client.
With this concern in mind, we have built this set of classes using Adapter pattern.
Currently the only implementation of client adapter in the library
is [GuzzleAdapter](../../src/Adapter/GuzzleAdapter.php).

## Basic Usage

The basic usage of client adapter consist of a few steps:

1. Initializing of a client adapter with connection configuration.
2. Creating of concrete **GatewayRequest**. Optionally any connection param can be overridden via config override.
3. Internally adapter has to do necessary operations to prepare **GatewayRequest** for processing - eg. transform it
   into request and extract necessary data.
4. Send request to remote URL and handle response - create EndpointResponse from it and return it to the higher context.
   Adapter should
   correctly handle all the error states.

## Adapter Config

Due to high diversity when it comes to client configuration, we have introduced a concept of client adapter config -
a class extended from the abstract contract [AbstractAdapterConfig](../../src/Adapter/Config/AbstractAdapterConfig.php)
implementing base contract [AdapterConfigInterface](../../src/Adapter/Config/AdapterConfigInterface.php).
Currently there is one concrete implementation coupled with **GuzzleAdapter**
- [GuzzleAdapterConfig](../../src/Adapter/Config/GuzzleAdapterConfig.php).

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use GuzzleHttp\RequestOptions;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use GuzzleHttp\Client;

$config = GuzzleAdapterConfig::createFromArray(
    [
        RequestOptions::TIMEOUT => 10.0,
        RequestOptions::CONNECT_TIMEOUT => 10.0,
    ]
);

$adapter = new GuzzleAdapter(
    new Client(),
    $config
);

$request = new TestRequest();
$response = $adapter->send($request);
if ($response->isSuccessful()) {
    //handle success
} else {
    //handle error
}
```
## Config Override
Adapter config illustrated in the previous section is the common way how to configure HTTP client adapter.
However it would be wastage of resources to instantiate an adapter per remote URL call. Due to this reason
a concept of config override was introduced - it is possible to have the standard adapter cofiguration that
can be overridden just in specific cases.
```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Adapter\Config\GuzzleAdapterConfig;
use GuzzleHttp\RequestOptions;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use GuzzleHttp\Client;

$standardConfig = GuzzleAdapterConfig::createFromArray(
    [
        RequestOptions::TIMEOUT => 10.0,
        RequestOptions::CONNECT_TIMEOUT => 10.0,
    ]
);

$adapter = new GuzzleAdapter(
    new Client(),
    $standardConfig
);

$standardRequest  = new TestRequest1();
$standardResponse = $adapter->send($standardRequest);

$configOverride = GuzzleAdapterConfig::createFromArray(
    [
            RequestOptions::TIMEOUT => 20.0,
            RequestOptions::CONNECT_TIMEOUT => 30.0,    
    ]
);

$overriddenResponse = $adapter->send($standardRequest, $configOverride);
```


`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Gateway](09-gateway.html)

# Gateway
The gateway contract is the "glue", that brings all the library concepts together. It
is responsible for sending the request, handling the communication (by delegation to the other components)
logging communication and returning the domain response by using mapper.
It is represented by interface [GatewayInterface](../../src/Transport/GatewayInterface.php)
and the implementation [Gateway](../../src/Transport/Gateway.php).
The contract also offers a way of changing the individual components of a remote communication - adapter and logger.
## Additional Functionality
As with almost every component, the gateway needed a way of "extending" the base functionality.
By offering base contract [GatewayInterface](../../src/Transport/GatewayInterface.php) it is possible to use
multiple suitable structural design patterns. By using this approach, we have implemented the
[Proxy](../../src/Transport/Proxy/GatewayCachingProxy.php) capable of caching of any successfully
performed request and store its response into [PSR cache]([GatewayInterface](../../src/Transport/GatewayInterface.php)).

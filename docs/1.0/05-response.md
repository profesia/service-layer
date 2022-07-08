`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Response](05-response.html)

# Gateway Response
Gateway response is a group of classes responsible for various jobs when in comes to handling of a response:
* Representation of a low-level connection response, from which connection information can be extracted.
* Representation of higher-level response aware of a project local domain.

The base interface for the response tree is minimalistic [GatewayResponseInterface](../../src/Response/GatewayResponseInterface.php).
It is responsible only for knowing whether a request itself was successful or not.
There are multiple definitions of a "successful" request and request`s successfulness is
heavily dependent on request context.
## Categorization
In the previous part th criteria for categorization are listed.
As stated, there are two types of response classes and both are extending original interface [GatewayResponseInterface](../../src/Response/GatewayResponseInterface.php):
* [EndpointResponseInterface](../../src/Response/Connection/EndpointResponseInterface.php) serves as low-level
communication response. It`s main responsibility is to wrap communication parameters - headers, status code and response body.
* [DomainResponseInterface](../../src/Response/Domain/DomainResponseInterface.php) should not contain
any low-level communication details, but rather serves as holder of a domain response. A domain response can vary
from case to case, so in this case, we have to lower the level of used strictness.
# Connection response
In this tree of classes, only one concrete implementation is present: [EndpointResponse](../../src/Response/Connection/EndpointResponse.php).
In our opinion, no other classes are needed for the low-level response manipulation. In fact, we don`t see
any reason for creation any new classes responsible for handling of the low-level communication. This type of response
class should be returned from [ClientAdapter](../../src/Adapter/AdapterInterface.php).
# Endpoint response
There are two concrete implementation in this tree of classes:
* [SimpleResponse](../../src/Response/Domain/SimpleResponse.php) responsible for wrapping of response body stream.
* [ErrorResponse](../../src/Response/Domain/ErrorResponse.php) responsible for wrapping of an exception instance.

those are already being used in the code base on the gateway level. **SimpleResponse** is returned on successful
endpoint call, when no mapper is set to gateway. **ErrorResponse** is returned on catching of 
a [ServiceLayerException](../../src/Exception/ServiceLayerException.php) instance, that was thrown during
any part of a remote communication.
## Usage
```php
<?php 

declare(strict_types=1);

use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;

final class SampleResponse implements DomainResponseInterface
{
    /**
     * Successfulness determined from higher context
     */
    private bool $isSuccessful = false;
    private int $a;
    private int $b;
    
    public function __construct(bool $isSuccessful, int $a, int $b)
    {
        $this->isSuccessful = $isSuccessful;
    }
    
    public function getResponseBody() {
        return new DomainObject(
            $this->a,
            $this->b
        );    
    }
    
    public function isSuccessful(): bool{
        return $this->isSuccessful;
    }
}

final class DomainObject
{
    private int $a;
    private int $b;
    
    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
    
    public function isGreater(): bool
    {
        return ($a > $b);
    }
}
```

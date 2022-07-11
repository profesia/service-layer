`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Gateway](07-mapper.html)

# Mapper

The role of a mapper contract amongst library contracts is straightforward - it maps/transforms
[EndpointResponse](../../src/Response/Connection/EndpointResponseInterface.php) onto the concrete
implementation of a [DomainResponse](../../src/Response/Domain/DomainResponseInterface.php).
By doing so, we prevent the end user to manipulate with the low-level communication details in
a higher context. The end user shouldn`t build any business logic around connection responses.
The only acceptable place of such task is the mapper contract.

## Basic Response Mapping

The handling of a connection response is highly dependant on a context. We are still working in the REST
context, but the handling of a call can differ based on parameters and response status code and response body -
eg. status code 404 can be acceptable as a happy day scenario when designing GET endpoint. However, when
it comes to PUT/DELETE, it would probably mean inconsistency on the system.

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface

final class SimpleMapper implements ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response) : DomainResponseInterface {
        if ($response->isSuccessful() === false) {
            //@TODO construct a concrete domain response or thrown an exception
        }
        
        //@TODO construct a domain response instance
    }    
}
```

The mapper contract is not strict - the end user can handle unsuccessful cases as he/she pleases - either by
handling all the scenarios with domain response DTO, or by throwing of an exception.
We highly recommend throwing of an exception in not valid states and design domain response as thin as possible.
This approach is going to make a domain response straightforward, readable and maintainable but is not usable in all
cases.

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface
use InvalidArgumentException;

final class SearchCaseMapper implements ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response) : DomainResponseInterface {
        $statusCode = $response->getStatusCode();
        
        if ($statusCode->equalsWithInt(404) === false && $response->isSuccessful() === false) {
            throw new InvalidArgumentException("Unsuccessful request");
        }
        
        $responseStream = $response->getBody();
        if ($responseStream->isSeekable()) {
            $responseStream->rewind();
        }
        
        return new DomainObject(
            $statusCode->equalsWithInt(404) !== false,
            json_decode($responseStream->getContents(), true, 512, JSON_THROW_ON_ERROR)
        );
    }    
}

final class DomainObject implements DomainResponseInterface
{
    private bool $wasFound;
    private array $rawResponseBody = [];
    
    public function __construct(bool $wasFound, array $rawResponseBody) 
    {
        $this->wasFound        = $wasFound;
        $this->rawResponseBody = $rawResponseBody;    
    }

    public function isSuccessful() : bool
    {
        //even 404 - Not Found is a valid response in this scenario
        return true;    
    }
    
    public function getResponseBody()
    {
        if ($this->wasFound === false) {
            return null;
        }
        
        return $this->rawResponseBody['user']['info'];
    }
}
```

## Advanced Domain Mapping

In some scenarios the mapper contract can serve as a validator for response body structure. There are
certain scenarios, when response structure is important. In those cases, it is mapper`s responsibility
to extract data from predefined structure or throw an exception, when the structure does not meet requirements.

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Mapper\ResponseDomainMapperInterface;
use Profesia\ServiceLayer\Response\Connection\EndpointResponseInterface
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface
use InvalidArgumentException;
use JsonException;

final class AdvancedMapper implements ResponseDomainMapperInterface
{
    public function mapToDomain(EndpointResponseInterface $response) : DomainResponseInterface {
        if ($response->isSuccessful() === false) {
            throw new InvalidArgumentException('Unsuccessful request');
        }
        
        $responseStream = $response->getBody();
        if ($responseStream->isSeekable()) {
            $responseStream->rewind();
        }
        
        try {
            $decodedJson = json_decode($responseStream->getContents(), true, 512, JSON_THROW_ON_ERROR);
            if (array_key_exists('responseBody', $decodedJson) === false) {
                
            }
            
            //runtime error on missing key
            $string = $decodedJson['responseBody']['stringData'];
            $array  = $decodedJson['responseBody']['arrayData'];
            $int    = $decodedJson['responseBody']['intData'];
            
            return new AdvancedDomainObject(
                $string,
                $array,
                $int    
            );
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Invalid JSON');    
        }

    }    
}

final class AdvancedDomainObject implements DomainResponseInterface
{
    private string $stringData;
    private array  $arrayData;
    private int    $intData;
    
    public function __construct(string $stringData, array $arrayData, int $intData)
    {
        $this->stringData = $stringData;
        $this->arrayData  = $arrayData;
        $this->intData    = $intData;
    }
    
    /**
      * @TODO  
     */
}
```

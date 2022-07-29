`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Gateway Registry](10-gateway-registry.html)

# Gateway Registry

By introducing the Gateway Registry we tried to solve the issue of class explosion when working
with the library. In almost every case the end user would need to create request factory and to inject
multiple classes to perform the remote endpoint call - gateway, mapper and request factory.

Moreover we think, that the vital part of the configuration should be rather placed
in the configuration parts of an application, not in the code itself.

## Gateway Use Case

The part of the Gateway Registry solution is the [contract](https://github.com/profesia/service-layer/blob/v0.9.0/src/Registry/GatewayUseCase.php)
representing on remote URL call with all the required classes and parameters.
The main responsibility of the class is the wrap all the library components to be able to execute
one endpoint remote call via one class.

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Registry\GatewayUseCase;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;

class RemoteIntegrationComponent
{
    private GatewayUseCase $useCase;
    
    public function __construct(GatewayUseCase $useCase)
    {
        $this->useCase = $useCase;
    }
    
    public function fetchResult(): DomainResponseInterface
    {
        return $this->useCase->performRequest();
    }
}
```

Also it offers auxiliary funcionality - the possibility to override each component
that is involved in the remote communication. The intent aims to have a standard configuration
the can be changed in the application config and also offers the end user to override
any communication detail, if needed.

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Registry\GatewayUseCase;
use Profesia\ServiceLayer\Response\Domain\DomainResponseInterface;
use Profesia\ServiceLayer\Adapter\AdapterInterface;
use Profesia\ServiceLayer\Request\AbstractGatewayRequest;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Nyholm\Psr7\Stream;

class RemoteIntegrationComponent
{
    private GatewayUseCase $useCase;
    private AdapterInterface $adapterOverride;
    private RequestFactoryInterface $psrRequestFactory;
    
    public function __construct(
        GatewayUseCase $useCase,
        AdapterInterface $adapterOverride,
        RequestFactoryInterface $psrRequestFactory
    )
    {
        $this->useCase           = $useCase;
        $this->adapterOverride   = $adapterOverride;
        $this->psrRequestFactory = $psrRequestFactory;
    }
    
    public function fetchResult(int $runtimeIntParam, string $runtimeStringParam): DomainResponseInterface
    {
        $this->useCase->viaAdapter($this->adapterOverride);
        $this->useCase->setRequestToSend(
            new TestRequest(
                $this->psrRequestFactory,
                $runtimeIntParam,
                $runtimeStringParam
            )
        );
        
        return $this->useCase->performRequest();
    }
}

class TestRequest extends AbstractGatewayRequest
{
    private int $intParam;
    private string $stringParam;

    public function __construct(RequestFactoryInterface $psrRequestFactory, int $intParam, strign $stringParam) {
        parent::__construct($psrRequestFactory);
        
        $this->intParam    = $intParam;
        $this->stringParam = $stringParam;
    }
    
    protected function getBody() : ?StreamInterface{
        return Stream::create(
            json_encode(
                [
                    'intParam'    => $this->intParam,
                    'stringParam' => $this->stringParam
                ]
            )
        );    
    }
    
    protected function getHeaders() : array{
        return [
            'Content-Type' => [
                'application/json'
            ]           
        ];
    }
}
```

## Registry

The main responsibility of the registry is to group similar requests and store pre-configured
gateway use cases and allow the end user to either perform a specific request by it`s name
or the extract pre-configured **GatewayUseCase** and inject it into a desired class instance.
This approach is suitable when working with the group of requests communicating with the
same system - int his scenario is logical to have one common configuration for the group of requests
and be able to tweak it in specific cases.
It also offers a static factory method via which the instance can be automatically created.
The sample configuration is illustrated on the following code sample:

```php
<?php

declare(strict_types=1);

use Profesia\ServiceLayer\Registry\GatewayUseCaseRegistry;

$registry = GatewayUseCaseRegistry::createFromArrayConfig(
    [
        'defaultGateway' => //GatewayInterface,
        'requests' => [
            'RequestName1' => [
                'request'         => //null|GatewayRequestInterface,
                'configOverride'  => //null|AdapterConfigInterface,
                'adapterOverride' => //null|AdapterInterface,
                'mapper'          => //null|ResponseDomainMapperInterface,
                'loggerOverride'  => //null|GatewayLoggerInterface,
                'gatewayOverride' => //null|GatewayInterface,
            ] 
        ]
    ]
);

$response = $registry->processUseCase('RequestName1');
//----------------------------------------------------
$useCase  = $registry->getConfiguredGatewayUseCase('RequestName1');
$response = $useCase->performRequest();
```
Any part in a concrete request configuration is optional and not required.


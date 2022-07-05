`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Request](04-request.html)

# Gateway Request

As stated in the [basic concepts](03-basic-concepts.html), request class
represents contract wrapping al the mandatory parameters for the API endpoint call
execution.

## Usage

### Request definition

Request definition is ### by method **toPsrRequest**. This method creates
PSR request instance, any PSR-18 compliant HTTP client should be able to process.
Basic request definition is illustrated on the following code sample:

```php
use Profesia\ServiceLayer\Transport\Request\GatewayRequestInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use Nyholm\Psr7\Stream;

final class TestRequest implements GatewayRequestInterface
{
    public function toPsrRequest(): RequestInterface
    {
        return new Request(
            'GET',
            'https://testuri.com/api/v1/test',
            [
                'Header1' => []
                'Header2' => []
                'Header3' => []
            ],
            Stream::create('Test request')
        );         
    }
}
```

PSR request creation is pretty straightforward, yet ### long to write part of a code.
There are more implementations available on the market and we do not want to force end user to use specific one.
To ensure easy-to-use request creation the library offers abstract
class [AbstractGatewayRequest](../../src/Transport/Request/AbstractGatewayRequest.php)
that splits request creation into separate parts - body definition, headers, definition, method definition etc:

```php
use Profesia\ServiceLayer\Transport\Request\AbstractGatewayRequest;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Nyholm\Psr7\Uri;

final class TestRequest extends AbstractGatewayRequest
{   
    protected function getMethod(): HttpMethod
    {
        return HttpMethod::createGet();
    }

    protected function getUri(): UriInterface
    {
        return new Uri('https://testuri.com/api/v1/test');
    }

    /**
     * @return string[][]
     */
    protected function getHeaders(): array
    {
        return             [
                'Header1' => []
                'Header2' => []
                'Header3' => []
            ];
    }

    protected function getBody(): ?StreamInterface
    {
        return Stream::create('Test request');
    }
}
```
Under the hood, by using 
[RequestFactoryInterface](https://www.php-fig.org/psr/psr-17/#21-requestfactoryinterface) implementation,
PSR request is created. Because the abstract class implements [GatewayRequestInterface](../../src/Transport/Request/GatewayRequestInterface.php),
interchangeability is ensured.
### Censoring critical data
The request implementation should contain all the necessary data for remote endpoint execution - including
credentials or other security sensitive data. The library comes with the logging of the request
contents out of-the-box. In other words, any part of the request is being written into the log. To overcome
security breaches, we have introduced a separated method family - "censored getters" those are being used under the
hood to retrieve information for recording.
Similarly as in request definition [AbstractGatewayRequest](../../src/Transport/Request/AbstractGatewayRequest.php)
comes with default behavior - each method returns its non-censored counterpart.
The specific request knows best what data to hide.
Basic usage is illustrated on the following code fragment:
```php
use Profesia\ServiceLayer\Transport\Request\AbstractGatewayRequest;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Nyholm\Psr7\Uri;

final class TestRequest extends AbstractGatewayRequest
{   
    private string $secret;
    
    public function __construct(RequestFactoryInterface $psrRequestFactory, string $secret)
    {
        $this->secret = $secret;
        parent::__construct($psrRequestFactory);
    }

    protected function getMethod(): HttpMethod
    {
        return HttpMethod::createGet();
    }

    protected function getUri(): UriInterface
    {
        return new Uri("https://testuri.com/api/v1/test/{$this->secret}");
    }

    /**
     * @return string[][]
     */
    protected function getHeaders(): array
    {
        return             [
                'Header1' => [
                    $this->secret
                ]
                'Header2' => []
                'Header3' => []
            ];
    }

    protected function getBody(): ?StreamInterface
    {
        return Stream::create("Test request, secret: {$this->secret}");
    }
    
    public function getCensoredUri(): UriInterface
    {
        return new Uri('https://testuri.com/api/v1/test/*****');
    }
    
    public function getCensoredHeaders(): array
    {
        $headers = $this->getHeaders();
        $headers['Header1'][0] = '*****';
        
        return $headers;
    }
    
    public function getCensoredBody(): ?StreamInterface
    {
        return Stream::create('Test request, secret: *****');    
    }
}
```
### Runtime params
There may occur situations when some parameters are not going to be available till runtime. In such situtions
usage of a request factory is advised as the best practise.

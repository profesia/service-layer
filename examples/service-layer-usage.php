<?php

declare(strict_types=1);

/**
 * Example usage of ServiceLayer facade for quick prototyping/testing
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Adapter\Config\AdapterConfig;
use Profesia\ServiceLayer\Adapter\GuzzleAdapter;
use Profesia\ServiceLayer\Facade\ServiceLayer;
use Profesia\ServiceLayer\Transport\Gateway;
use Profesia\ServiceLayer\Transport\Logging\CommunicationLogger;
use Profesia\ServiceLayer\ValueObject\HttpMethod;
use Psr\Log\NullLogger;

// Set up dependencies
$client = new Client();
$adapter = new GuzzleAdapter($client, AdapterConfig::createDefault());
$logger = new CommunicationLogger(new NullLogger());
$gateway = new Gateway($adapter, $logger);
$requestFactory = new Psr17Factory();

// Create a facade instance
$facade = new ServiceLayer($gateway, $requestFactory);

// Example 1: Simple GET request
echo "Example 1: GET request\n";
try {
    $uri = new Uri('https://api.example.com/users');
    $response = $facade->executeRequest($uri, HttpMethod::createGet());
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
    echo "Body: " . $response->getResponseBody() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 2: POST request with JSON body
echo "Example 2: POST request with body\n";
try {
    $uri = new Uri('https://api.example.com/users');
    $body = Stream::create(json_encode(['name' => 'John Doe', 'email' => 'john@example.com']));
    
    $response = $facade->executeRequest($uri, HttpMethod::createPost(), $body);
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 3: PUT request
echo "Example 3: PUT request\n";
try {
    $uri = new Uri('https://api.example.com/users/123');
    $body = Stream::create(json_encode(['status' => 'active']));
    
    $response = $facade->executeRequest($uri, HttpMethod::createPut(), $body);
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 4: DELETE request
echo "Example 4: DELETE request\n";
try {
    $uri = new Uri('https://api.example.com/users/123');
    $response = $facade->executeRequest($uri, HttpMethod::createDelete());
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 5: Request with client options (timeout, verify SSL, etc.)
echo "Example 5: Request with custom client options\n";
try {
    $uri = new Uri('https://api.example.com/users');
    $clientOptions = [
        'timeout' => 10.0,
        'connect_timeout' => 5.0,
        'verify' => false,
    ];
    
    $response = $facade->executeRequest($uri, HttpMethod::createGet(), null, $clientOptions);
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 6: Using builder pattern with custom mapper
echo "Example 6: Builder pattern with custom response mapper\n";
try {
    $uri = new Uri('https://api.example.com/users');
    
    $response = $facade
        ->withMapper(function ($endpointResponse) {
            // Custom transformation logic
            echo "Custom mapper called\n";
            return \Profesia\ServiceLayer\Response\Domain\SimpleResponse::createFromEndpointResponse($endpointResponse);
        })
        ->executeRequest($uri, HttpMethod::createGet());
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 7: Using builder pattern with client options (state resets after request)
echo "Example 7: Builder pattern with client options (auto-reset)\n";
try {
    $uri = new Uri('https://api.example.com/users');
    
    // First request with client options
    $response = $facade
        ->withClientOptions(['timeout' => 15.0, 'verify' => false])
        ->executeRequest($uri, HttpMethod::createGet());
    
    echo "First request successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
    
    // Second request - state is automatically reset, so default options apply
    $response = $facade->executeRequest($uri, HttpMethod::createGet());
    
    echo "Second request successful (default options): " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

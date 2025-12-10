<?php

declare(strict_types=1);

/**
 * Example usage of SimpleFacade for quick prototyping/testing
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Profesia\ServiceLayer\Transport\SimpleFacade;
use Profesia\ServiceLayer\ValueObject\HttpMethod;

// Create a simple facade instance with default settings
$facade = new SimpleFacade();

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

// Example 7: Using builder pattern with client options
echo "Example 7: Builder pattern with client options\n";
try {
    $uri = new Uri('https://api.example.com/users');
    
    $response = $facade
        ->withClientOptions(['timeout' => 15.0, 'verify' => false])
        ->executeRequest($uri, HttpMethod::createGet());
    
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}



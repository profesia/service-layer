<?php

declare(strict_types=1);

/**
 * Example usage of SimpleFacade for quick prototyping/testing
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Stream;
use Profesia\ServiceLayer\Transport\SimpleFacade;

// Create a simple facade instance with default settings
$facade = new SimpleFacade();

// Example 1: Simple GET request
echo "Example 1: GET request\n";
try {
    $response = $facade->executeRequest(
        'https://api.example.com/users',
        'GET'
    );
    
    echo "Status: " . $response->getStatusCode()->getValue() . "\n";
    echo "Body: " . $response->getResponseBody()->getContents() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 2: POST request with JSON body
echo "Example 2: POST request with body\n";
try {
    $body = Stream::create(json_encode(['name' => 'John Doe', 'email' => 'john@example.com']));
    
    $response = $facade->executeRequest(
        'https://api.example.com/users',
        'POST',
        $body
    );
    
    echo "Status: " . $response->getStatusCode()->getValue() . "\n";
    echo "Successful: " . ($response->isSuccessful() ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 3: PUT request
echo "Example 3: PUT request\n";
try {
    $body = Stream::create(json_encode(['status' => 'active']));
    
    $response = $facade->executeRequest(
        'https://api.example.com/users/123',
        'put',  // Method names are case-insensitive
        $body
    );
    
    echo "Status: " . $response->getStatusCode()->getValue() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 4: DELETE request
echo "Example 4: DELETE request\n";
try {
    $response = $facade->executeRequest(
        'https://api.example.com/users/123',
        'DELETE'
    );
    
    echo "Status: " . $response->getStatusCode()->getValue() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

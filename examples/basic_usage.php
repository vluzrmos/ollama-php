<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\Ollama;
use Ollama\Models\Message;

// Configure client
$client = new Ollama(getenv('OLLAMA_API_URL') ?: 'http://localhost:11434');

$defaultModel = 'qwen2.5:3b'; // Default model for examples
echo "=== Example 1: Generate Completion ===\n";
try {
    $response = $client->generate([
        'model' => $defaultModel,
        'prompt' => 'Why is the sky blue?',
        'stream' => false
    ]);
    
    echo "Response: " . $response['response'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 2: Chat Completion ===\n";
try {
    $messages = [
        Message::user('Hello, how are you?'),
    ];
    
    $response = $client->chat([
        'model' => $defaultModel,
        'messages' => array_map(function($msg) { return $msg->toArray(); }, $messages),
        'stream' => false
    ]);
    
    echo "Response: " . $response['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 3: Chat with History ===\n";
try {
    $messages = [
        Message::system('You are a helpful assistant that responds in English.'),
        Message::user('What is the capital of Brazil?'),
        Message::assistant('The capital of Brazil is Brasília.'),
        Message::user('What is the population of that city?')
    ];
    
    $response = $client->chat([
        'model' => $defaultModel,
        'messages' => array_map(function($msg) { return $msg->toArray(); }, $messages),
        'stream' => false
    ]);
    
    echo "Response: " . $response['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 4: Streaming ===\n";
try {
    echo "Streaming response: ";
    $client->generate([
        'model' => $defaultModel,
        'prompt' => 'Tell a short joke',
        'stream' => true
    ], function($chunk) {
        if (isset($chunk['response'])) {
            echo $chunk['response'];
        }
    });
    echo "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 5: Tool Calling ===\n";
try {
    $tools = [
        
    ];
    
    $response = $client->chat([
        'model' => $defaultModel,
        'messages' => [
            Message::user('What is the weather in São Paulo today?')->toArray()
        ],
        'tools' => $tools,
        'stream' => false
    ]);
    
    echo "Tool calls: " . json_encode($response['message'], JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 6: List Models ===\n";
try {
    $models = $client->listModels();
    echo "Available models:\n";
    foreach ($models['models'] as $model) {
        echo "- " . $model['name'] . " (" . round($model['size'] / 1024 / 1024 / 1024, 2) . " GB)\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 7: Embeddings ===\n";
try {
    $response = $client->embeddings([
        'model' => 'all-minilm',
        'input' => 'This is a text to generate embeddings'
    ]);
    
    echo "Generated embeddings: " . count($response['embeddings'][0]) . " dimensions\n";
    echo "First 5 dimensions: " . json_encode(array_slice($response['embeddings'][0], 0, 5)) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 8: Model Information ===\n";
try {
    $info = $client->showModel($defaultModel);
    echo "Model information:\n";
    echo "- Family: " . (isset($info['details']['family']) ? $info['details']['family'] : 'N/A') . "\n";
    echo "- Parameters: " . (isset($info['details']['parameter_size']) ? $info['details']['parameter_size'] : 'N/A') . "\n";
    echo "- Format: " . (isset($info['details']['format']) ? $info['details']['format'] : 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example 9: Ollama Version ===\n";
try {
    $version = $client->version();
    echo "Ollama version: " . $version['version'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}
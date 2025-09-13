<?php

/**
 * Simple Agent Example
 * 
 * This example demonstrates basic agent usage with minimal setup
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Agents\Agent;
use Vluzrmos\Ollama\Agents\AgentGroup;
use Vluzrmos\Ollama\Agents\OpenAIClientAdapter;
use Vluzrmos\Ollama\Tools\TimeTool;

// Simple configuration
$client = new OpenAI(
    getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1',
    getenv('OPENAI_API_KEY') ?: 'ollama'
);
$adapter = new OpenAIClientAdapter($client);
$model = getenv('TEST_MODEL') ?: 'qwen2.5:3b';

echo "=== Simple Agent Example ===\n";

// Create a helpful assistant agent
$assistant = new Agent(
    'Assistant',
    $adapter,
    $model,
    'You are a helpful AI assistant. Provide clear, accurate, and helpful responses.',
    'General purpose AI assistant',
    [new TimeTool()] // Add time tool
);

// Test the assistant
echo "Testing single agent:\n";
$response = $assistant->processQuery("What time is it?");

if (is_object($response) && method_exists($response, 'toArray')) {
    $responseArray = $response->toArray();
    if (isset($responseArray['choices'][0]['message']['content'])) {
        echo "Response: " . $responseArray['choices'][0]['message']['content'] . "\n\n";
    }
}

// Create a specialist agent
$mathAgent = new Agent(
    'MathExpert',
    $adapter,
    $model,
    'You are a mathematics expert. Solve problems step by step.',
    'Mathematics specialist'
);

// Create agent group
$group = new AgentGroup(
    'Assistants',
    $adapter,
    $model,
    [$assistant, $mathAgent],
    'A group of helpful assistants'
);

// Test the group
echo "Testing agent group:\n";
$response = $group->processQuery("What is 15 + 27?");

if (is_object($response) && method_exists($response, 'toArray')) {
    $responseArray = $response->toArray();
    if (isset($responseArray['choices'][0]['message']['content'])) {
        echo "Response: " . $responseArray['choices'][0]['message']['content'] . "\n";
    }
}

echo "\n=== Example Complete ===\n";

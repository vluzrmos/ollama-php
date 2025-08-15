<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/tools/CalculatorTool.php';
require_once __DIR__ . '/tools/WeatherTool.php';

use Vluzrmos\Ollama\Tools\ToolManager;
use Examples\Tools\CalculatorTool;
use Examples\Tools\WeatherTool;

echo "=== Quick Tool System Test ===\n\n";

// Create manager and register tools
$toolManager = new ToolManager();
$toolManager->registerTool(new CalculatorTool());
$toolManager->registerTool(new WeatherTool());

// Simulate tool calls as they would come from API
$toolCalls = array(
    array(
        'id' => 'call_calc_001',
        'type' => 'function',
        'function' => array(
            'name' => 'calculator',
            'arguments' => '{"operation": "multiply", "a": 25, "b": 4}'
        )
    ),
    array(
        'id' => 'call_weather_001',
        'type' => 'function',
        'function' => array(
            'name' => 'get_current_weather',
            'arguments' => '{"location": "São Paulo", "unit": "celsius"}'
        )
    )
);

echo "Tool Calls to execute:\n";
foreach ($toolCalls as $call) {
    echo "- {$call['function']['name']}: {$call['function']['arguments']}\n";
}
echo "\n";

// Execute tool calls
echo "Executing tool calls...\n\n";
$results = $toolManager->executeToolCalls($toolCalls);

// Show results
foreach ($results as $result) {
    echo "=== {$result['tool_name']} ===\n";
    echo "ID: {$result['id']}\n";
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    
    if ($result['success']) {
        echo "Result:\n{$result['result']}\n";
    } else {
        echo "Error: {$result['error']}\n";
    }
    echo "\n";
}

// Convert to messages
echo "=== Convert to Messages ===\n";
$messages = $toolManager->toolCallResultsToMessages($results);

foreach ($messages as $i => $message) {
    echo "Message " . ($i + 1) . ":\n";
    echo "- Role: {$message['role']}\n";
    echo "- Content: {$message['content']}\n";
    if (isset($message['tool_call_id'])) {
        echo "- Tool Call ID: {$message['tool_call_id']}\n";
    }
    if (isset($message['tool_name'])) {
        echo "- Tool Name: {$message['tool_name']}\n";
    }
    echo "\n";
}

echo "=== Test Complete ===\n";

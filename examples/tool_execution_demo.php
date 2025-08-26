<?php

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Bahia');

require_once __DIR__ . '/tools/CalculatorTool.php';
require_once __DIR__ . '/tools/WeatherTool.php';

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Tools\ToolManager;
use Examples\Tools\CalculatorTool;
use Examples\Tools\WeatherTool;

echo "=== Tool System Demonstration ===\n\n";

// Configure the OpenAI compatible client
$openai = new OpenAI(getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1', 'ollama');
$model = new Model('qwen2.5:3b');

// Configure the tool manager
$toolManager = new ToolManager();
$toolManager->registerTool(new CalculatorTool());
$toolManager->registerTool(new WeatherTool());

echo "Registered tools:\n";
foreach ($toolManager->listTools() as $toolName) {
    echo "- $toolName\n";
}
echo "\n";

// Example 1: Simulated API tool calls
echo "=== Example 1: Execute Simulated Tool Calls ===\n";

$simulatedToolCalls = array(
    array(
        'id' => 'call_001',
        'type' => 'function',
        'function' => array(
            'name' => 'calculator',
            'arguments' => json_encode(array(
                'operation' => 'add',
                'a' => 15,
                'b' => 27
            ))
        )
    ),
    array(
        'id' => 'call_002', 
        'type' => 'function',
        'function' => array(
            'name' => 'get_current_weather',
            'arguments' => json_encode(array(
                'location' => 'São Paulo, SP',
                'unit' => 'celsius'
            ))
        )
    )
);

echo "Executing tool calls...\n";
$results = $toolManager->executeToolCalls($simulatedToolCalls);

foreach ($results as $result) {
    echo "Tool: " . $result['tool_name'] . "\n";
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    
    if ($result['success']) {
        echo "Result: " . json_encode($result['result']) . "\n";
    } else {
        echo "Error: " . $result['error'] . "\n";
    }
    echo "---\n";
}

// Convert results to message format
echo "\n=== Convert to Messages ===\n";
$messages = $toolManager->toolCallResultsToMessages($results);

foreach ($messages as $message) {
    echo "Role: " . $message['role'] . "\n";
    echo "Content: " . $message['content'] . "\n";
    if (isset($message['tool_call_id'])) {
        echo "Tool Call ID: " . $message['tool_call_id'] . "\n";
    }
    if (isset($message['tool_name'])) {
        echo "Tool Name: " . $message['tool_name'] . "\n";
    }
    echo "---\n";
}

// Example 2: Real OpenAI integration (if available)
echo "\n=== Example 2: OpenAI API Integration ===\n";

try {
    $response = $openai->chatCompletions(array(
        'model' => $model->getName(),
        'messages' => array(
            array('role' => 'system', 'content' => 'You are a helpful assistant that can use tools to perform calculations and get weather information.'),
            array('role' => 'user', 'content' => 'What is 25 + 17? And how is the weather in Rio de Janeiro?')
        ),
        'tools' => $toolManager->toArray(),
        'temperature' => 0.1
    ));

    if (isset($response['choices'][0]['message']['tool_calls'])) {
        $toolCalls = $response['choices'][0]['message']['tool_calls'];
        
        echo "The model requested execution of " . count($toolCalls) . " tool(s):\n";
        foreach ($toolCalls as $toolCall) {
            echo "- " . $toolCall['function']['name'] . "\n";
        }
        
        echo "\nExecuting tools...\n";
        $toolResults = $toolManager->executeToolCalls($toolCalls);
        
        // Show results
        foreach ($toolResults as $result) {
            echo "\nTool: " . $result['tool_name'] . "\n";
            if ($result['success']) {
                echo "Result: " . json_encode($result['result']) . "\n";
            } else {
                echo "Error: " . $result['error'] . "\n";
            }
        }
        
        // Convert to messages and send back to the model
        $toolMessages = $toolManager->toolCallResultsToMessages($toolResults);
        
        $messages = array(
            array('role' => 'system', 'content' => 'You are a helpful assistant that can use tools to perform calculations and get weather information.'),
            array('role' => 'user', 'content' => 'What is 25 + 17? And how is the weather in Rio de Janeiro?'),
            $response['choices'][0]['message'] // Add the original response with tool_calls
        );
        
        // Add tool results
        foreach ($toolMessages as $toolMessage) {
            $messages[] = $toolMessage;
        }
        
        echo "\nSending tool results back to the model...\n";
        $finalResponse = $openai->chatCompletions(array(
            'model' => $model->getName(),
            'messages' => $messages,
            'temperature' => 0.1
        ));
        
        echo "\nFinal model response:\n";
        echo $finalResponse['choices'][0]['message']['content'] . "\n";
        
    } else {
        echo "The model did not request tool usage.\n";
        echo "Response: " . $response['choices'][0]['message']['content'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error in API integration: " . $e->getMessage() . "\n";
    echo "This is normal if the Ollama server is not running.\n";
}

// Example 3: Error handling
echo "\n=== Example 3: Error Handling ===\n";

$invalidToolCalls = array(
    array(
        'id' => 'call_003',
        'type' => 'function',
        'function' => array(
            'name' => 'non_existent_tool',
            'arguments' => '{}'
        )
    ),
    array(
        'id' => 'call_004',
        'type' => 'function', 
        'function' => array(
            'name' => 'calculator',
            'arguments' => '{invalid json'
        )
    ),
    array(
        'id' => 'call_005',
        'type' => 'function',
        'function' => array(
            'name' => 'calculator',
            'arguments' => json_encode(array(
                'operation' => 'divide',
                'a' => 10,
                'b' => 0  // Division by zero
            ))
        )
    )
);

echo "Testing tool calls with errors...\n";
$errorResults = $toolManager->executeToolCalls($invalidToolCalls);

foreach ($errorResults as $result) {
    echo "Tool: " . (isset($result['tool_name']) ? $result['tool_name'] : 'unknown') . "\n";
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    
    if (!$result['success']) {
        echo "Error: " . $result['error'] . "\n";
    }
    echo "---\n";
}

echo "\n=== Tool Manager Statistics ===\n";
$stats = $toolManager->getStats();
echo "Total tools: " . $stats['total_tools'] . "\n";
echo "Available tools:\n";
foreach ($stats['tools'] as $toolName => $toolInfo) {
    echo "- {$toolInfo['name']}: {$toolInfo['description']} ({$toolInfo['parameters_count']} parameters)\n";
}

echo "\n=== Demonstration Complete ===\n";

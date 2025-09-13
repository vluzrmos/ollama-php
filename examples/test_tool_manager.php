<?php
/**
 * Test the new ToolManager integration in Agent
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Agents\Agent;
use Vluzrmos\Ollama\Agents\OpenAIClientAdapter;
use Vluzrmos\Ollama\Tools\TimeTool;
use Vluzrmos\Ollama\Tools\ToolManager;

echo "=== Testing Agent with ToolManager ===\n";

// Setup client
$client = new OpenAI(
    getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1',
    getenv('OPENAI_API_KEY') ?: 'ollama'
);
$adapter = new OpenAIClientAdapter($client);
$model = getenv('TEST_MODEL') ?: 'qwen2.5:3b';

// Test 1: Agent with array of tools (should create ToolManager internally)
echo "1. Creating agent with array of tools...\n";
$agent1 = new Agent(
    'TestAgent1',
    $adapter,
    $model,
    'You are a helpful assistant.',
    'Test agent',
    [new TimeTool()]
);

echo "Tools in agent1: " . count($agent1->getTools()->listTools()) . "\n";
echo "Tool names: " . implode(', ', $agent1->getTools()->listTools()) . "\n";

// Test 2: Agent with ToolManager instance
echo "\n2. Creating agent with ToolManager instance...\n";
$toolManager = new ToolManager();
$toolManager->registerTool(new TimeTool());

$agent2 = new Agent(
    'TestAgent2',
    $adapter,
    $model,
    'You are a helpful assistant.',
    'Test agent',
    $toolManager
);

echo "Tools in agent2: " . count($agent2->getTools()->listTools()) . "\n";
echo "Tool names: " . implode(', ', $agent2->getTools()->listTools()) . "\n";

// Test 3: Using agent methods to manage tools
echo "\n3. Testing tool management methods...\n";
$agent3 = new Agent(
    'TestAgent3',
    $adapter,
    $model,
    'You are a helpful assistant.',
    'Test agent',
    [] // No tools initially
);

echo "Initial tools: " . count($agent3->getTools()->listTools()) . "\n";

// Add tool using agent method
$agent3->addTool(new TimeTool());
echo "After adding TimeTool: " . count($agent3->getTools()->listTools()) . "\n";

// Test using ToolManager directly
$agent3->getTools()->registerTool(new TimeTool()); // Won't duplicate
echo "After trying to add same tool: " . count($agent3->getTools()->listTools()) . "\n";

// Remove tool
$removed = $agent3->removeTool('get_current_time');
echo "Removed tool: " . ($removed ? 'yes' : 'no') . "\n";
echo "Final tools: " . count($agent3->getTools()->listTools()) . "\n";

echo "\n=== Test Complete ===\n";
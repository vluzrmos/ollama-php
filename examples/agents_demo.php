<?php
/**
 * Example demonstrating how to use Agents and AgentGroups
 * 
 * This example shows:
 * - Creating individual agents with specific roles and tools
 * - Creating an agent group that coordinates between agents
 * - Using both individual agents and agent groups
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Ollama;
use Vluzrmos\Ollama\Agents\Agent;
use Vluzrmos\Ollama\Agents\AgentGroup;
use Vluzrmos\Ollama\Agents\OpenAIClientAdapter;
use Vluzrmos\Ollama\Agents\OllamaClientAdapter;
use Vluzrmos\Ollama\Tools\TimeTool;

// Initialize clients
$openaiClient = new OpenAI(
    getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1',
    getenv('OPENAI_API_KEY') ?: 'ollama'
);

$ollamaClient = new Ollama(
    getenv('OLLAMA_API_URL') ?: 'http://localhost:11434'
);

// Create client adapters
$openaiAdapter = new OpenAIClientAdapter($openaiClient);
$ollamaAdapter = new OllamaClientAdapter($ollamaClient);

// Get model name from environment
$model = getenv('TEST_MODEL') ?: 'qwen2.5:3b';

echo "=== Agent System Demo ===\n";
echo "Using model: {$model}\n\n";

// Example 1: Create a Math Agent with specific tools
echo "1. Creating a Math Agent...\n";

$mathAgent = new Agent(
    'MathExpert',
    $openaiAdapter,
    $model,
    'You are a mathematics expert. You specialize in solving mathematical problems, ' .
    'calculations, equations, and providing mathematical explanations. ' .
    'Always show your work and explain your reasoning step by step.',
    'Expert in mathematics, calculations, and problem solving',
    [new TimeTool()], // Example tool
    ['temperature' => 0.1] // Lower temperature for more precise math
);

// Test the math agent
$mathQuery = "What is the derivative of x^3 + 2x^2 - 5x + 3?";
echo "Query to Math Agent: {$mathQuery}\n";

try {
    $mathResponse = $mathAgent->processQuery($mathQuery);
    echo "Math Agent Response: ";
    
    if (is_object($mathResponse) && method_exists($mathResponse, 'toArray')) {
        $responseArray = $mathResponse->toArray();
        if (isset($responseArray['choices'][0]['message']['content'])) {
            echo $responseArray['choices'][0]['message']['content'] . "\n\n";
        } elseif (isset($responseArray['message']['content'])) {
            echo $responseArray['message']['content'] . "\n\n";
        }
    } else {
        echo json_encode($mathResponse, JSON_PRETTY_PRINT) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error with Math Agent: " . $e->getMessage() . "\n\n";
}

// Example 2: Create a Creative Writing Agent
echo "2. Creating a Creative Writing Agent...\n";

$writerAgent = new Agent(
    'CreativeWriter',
    $ollamaAdapter,
    $model,
    'You are a creative writing expert. You excel at storytelling, poetry, ' .
    'creative content, and literary analysis. You write with imagination, ' .
    'style, and emotional depth.',
    'Expert in creative writing, storytelling, and literature',
    [], // No specific tools for this example
    ['temperature' => 0.8] // Higher temperature for creativity
);

// Test the writer agent
$writerQuery = "Write a short poem about artificial intelligence";
echo "Query to Writer Agent: {$writerQuery}\n";

try {
    $writerResponse = $writerAgent->processQuery($writerQuery);
    echo "Writer Agent Response: ";
    
    if (is_object($writerResponse) && method_exists($writerResponse, 'toArray')) {
        $responseArray = $writerResponse->toArray();
        if (isset($responseArray['choices'][0]['message']['content'])) {
            echo $responseArray['choices'][0]['message']['content'] . "\n\n";
        } elseif (isset($responseArray['message']['content'])) {
            echo $responseArray['message']['content'] . "\n\n";
        }
    } else {
        echo json_encode($writerResponse, JSON_PRETTY_PRINT) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error with Writer Agent: " . $e->getMessage() . "\n\n";
}

// Example 3: Create an Agent Group
echo "3. Creating an Agent Group with multiple specialists...\n";

$agentGroup = new AgentGroup(
    'AIAssistants',
    $openaiAdapter, // Use for coordination
    $model,
    [$mathAgent, $writerAgent],
    'A group of specialized AI assistants that can handle various types of queries'
);

// Test the agent group with different types of queries
$testQueries = [
    "What is 25 * 47?",
    "Write a haiku about programming",
    "Hello, how are you today?",
    "Solve for x: 2x + 5 = 13"
];

foreach ($testQueries as $index => $query) {
    echo "Query " . ($index + 1) . " to Agent Group: {$query}\n";
    
    try {
        $groupResponse = $agentGroup->processQuery($query);
        echo "Agent Group Response: ";
        
        if (is_object($groupResponse) && method_exists($groupResponse, 'toArray')) {
            $responseArray = $groupResponse->toArray();
            if (isset($responseArray['choices'][0]['message']['content'])) {
                echo $responseArray['choices'][0]['message']['content'] . "\n";
            } elseif (isset($responseArray['message']['content'])) {
                echo $responseArray['message']['content'] . "\n";
            }
        } else {
            echo json_encode($groupResponse, JSON_PRETTY_PRINT) . "\n";
        }
        echo str_repeat('-', 50) . "\n";
    } catch (Exception $e) {
        echo "Error with Agent Group: " . $e->getMessage() . "\n";
        echo str_repeat('-', 50) . "\n";
    }
}

// Example 4: Dynamic agent management
echo "\n4. Demonstrating dynamic agent management...\n";

// Create a new tech agent
$techAgent = new Agent(
    'TechExpert',
    $openaiAdapter,
    $model,
    'You are a technology expert specializing in programming, software development, ' .
    'system architecture, and technical problem-solving.',
    'Expert in technology, programming, and software development'
);

// Add the tech agent to the group
$agentGroup->addAgent($techAgent);
echo "Added TechExpert to the group. Available agents: ";
foreach ($agentGroup->getAgents() as $agent) {
    echo $agent->getName() . " ";
}
echo "\n";

// Test with a technical query
$techQuery = "What are the benefits of microservices architecture?";
echo "Technical query: {$techQuery}\n";

try {
    $techResponse = $agentGroup->processQuery($techQuery);
    echo "Response: ";
    
    if (is_object($techResponse) && method_exists($techResponse, 'toArray')) {
        $responseArray = $techResponse->toArray();
        if (isset($responseArray['choices'][0]['message']['content'])) {
            echo $responseArray['choices'][0]['message']['content'] . "\n";
        } elseif (isset($responseArray['message']['content'])) {
            echo $responseArray['message']['content'] . "\n";
        }
    } else {
        echo json_encode($techResponse, JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Agent System Demo Complete ===\n";
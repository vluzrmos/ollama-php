<?php

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Bahia');

require __DIR__ . '/tools/CalculatorTool.php';
require __DIR__ . '/tools/WeatherTool.php';

use Examples\Tools\CalculatorTool;
use Examples\Tools\WeatherTool;
use Vluzrmos\Ollama\Ollama;
use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Tools\TimeTool;
use Vluzrmos\Ollama\Tools\ToolManager;
use Vluzrmos\Ollama\Utils\ImageHelper;

/**
 * Advanced example: Interactive chat system with tools
 */
class ChatSystem
{
    private $client;
    private $messages;

    /**
     * @var ToolManager
     */
    private $tools;

    public function __construct()
    {
        $this->client = new Ollama(getenv('OLLAMA_API_URL') ?: 'http://localhost:11434');
        $this->messages = array();
        $this->setupTools();
    }

    private function setupTools()
    {
        $this->tools = new ToolManager();

        $this->tools->registerTool(new CalculatorTool());
        $this->tools->registerTool(new WeatherTool());
        $this->tools->registerTool((new TimeTool())->setTimezone('America/Sao_Paulo'));
    }

    public function addSystemMessage($content)
    {
        $this->messages[] = Message::system($content);
    }

    public function chat($userMessage, $model = 'qwen2.5:3b', array $images = null)
    {
        // Add user message
        $this->messages[] = Message::user($userMessage, $images);

        try {
            $params = array(
                'model' => $model,
                'messages' => $this->prepareMessages(),
                'stream' => false
            );

            if (!$images) {
                $params['tools'] = $this->tools->jsonSerialize();
            }

            $response = $this->client->chat($params);

            $assistantMessage = $response['message'];

            // Check if the model wants to use tools
            if (isset($assistantMessage['tool_calls']) && !empty($assistantMessage['tool_calls'])) {                
                return $this->handleToolCalls($assistantMessage['tool_calls'], $model);
            } else {
                // Add assistant response to history
                $this->messages[] = Message::assistant($assistantMessage['content']);
                return $assistantMessage['content'];
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    private function handleToolCalls($toolCalls, $model)
    {
        $this->messages = array_merge(
            $this->messages,
            $this->tools->toolCallResultsToMessages(
                $this->tools->executeToolCalls($toolCalls)
            )
        );
        
        // Make new call to model with tool results
        try {
            $response = $this->client->chat(array(
                'model' => $model,
                'messages' => $this->prepareMessages(),
                'tools' => $this->tools,
                'stream' => false
            ));

            $finalMessage = $response['message']['content'];
            $this->messages[] = Message::assistant($finalMessage);

            return $finalMessage;
        } catch (Exception $e) {
            return "Error processing tool results: " . $e->getMessage();
        }
    }

    private function prepareMessages()
    {
        return $this->messages;
    }

    public function getConversationHistory()
    {
        return $this->messages;
    }

    public function clearHistory()
    {
        $this->messages = array();
    }
}

// Usage example
echo "=== Interactive Chat System with Tools ===\n\n";

$chatSystem = new ChatSystem();
$chatSystem->addSystemMessage('You are a helpful assistant that can use tools to get information about weather, do calculations, and get current date/time. Always respond in English.');

// Conversation simulation
$conversations = array(
    "What is the weather in São Paulo today?",
    "What is 15 + 27?",
    "What time is it now?",
    "Calculate the square root of 144",
    "How is the weather in Rio de Janeiro in Fahrenheit?",
    "Thank you for your help!"
);

foreach ($conversations as $userInput) {
    echo "User: $userInput\n";
    $response = $chatSystem->chat($userInput);
    echo "Assistant: $response\n\n";

    // Small pause to simulate real conversation
    sleep(1);
}

echo "=== Example Chat with Images ===\n";

$response = $chatSystem->chat('What is in the image?', 'qwen2.5vl:3b', [ImageHelper::encodeImage(__DIR__.'/sample-720p.jpg')]);
echo "Response: $response\n\n";

$response = $chatSystem->chat('How many people are in the image?', 'qwen2.5vl:3b', [ImageHelper::encodeImage(__DIR__.'/sample-720p.jpg')]);
echo "Response: $response\n\n";

echo "=== Conversation History ===\n";
$history = $chatSystem->getConversationHistory();
foreach ($history as $i => $message) {    
    echo ($i + 1) . ". [{$message->role}]: {$message->content}\n";
    if ($message->toolName) {
        echo "   (Tool: {$message->toolName})\n";
    }
}

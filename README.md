# Ollama PHP Client

PHP client for Ollama/OpenAI API, compatible with PHP 5.6+. This library provides an easy-to-use interface to interact with Ollama server and also includes OpenAI API compatibility.

## Features

- ✅ Compatible with PHP 5.6+
- ✅ Full support for Ollama native API
- ✅ **New**: OpenAI API endpoints compatibility
- ✅ **New**: `Model` class for reusable model configuration
- ✅ Chat completions with history
- ✅ Response streaming
- ✅ Image support (vision models like Llava)
- ✅ Function calling (tools)
- ✅ Embeddings
- ✅ Model management
- ✅ Robust error handling
- ✅ Complete documentation

## Installation

```bash
composer require vluzrrmos/ollama-php
```

## Quick Usage

### Ollama Client (Native API)

```php
<?php

require_once 'vendor/autoload.php';

use Vluzrmos\Ollama\Ollama;
use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Models\Model;

// Create client
$ollama = new Ollama('http://localhost:11434');

// Simple chat
$response = $ollama->chat([
    'model' => 'llama3.2',
    'messages' => [
        Message::system('You are a helpful assistant.')->toArray(),
        Message::user('Hello!')->toArray()
    ]
]);

echo $response['message']['content'];
```

### OpenAI Client (Compatible)

```php
<?php
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Model;

// Create OpenAI compatible client
$openai = new OpenAI('http://localhost:11434/v1', 'ollama');

// Chat using OpenAI methods
$response = $openai->chat('llama3.2', [
    $openai->systemMessage('You are a helpful assistant.'),
    $openai->userMessage('Hello!')
]);

echo $response['choices'][0]['message']['content'];
```

### Model Class for Reuse

```php
<?php
use Vluzrmos\Ollama\Models\Model;

// Create model
$model = (new Model('llama3.2'))
    ->setTemperature(0.8)
    ->setTopP(0.9)
    ->setNumCtx(4096)
    ->setSeed(42);

// Use with OpenAI client
$response = $openai->chat($model, [
    $openai->userMessage('Tell me a story')
]);

// Or use with Ollama client
$params = array_merge($model->toArray(), [
    'messages' => [Message::user('Tell me a story')->toArray()]
]);
$response = $ollama->chat($params);
```

## Advanced Examples

### Streaming

```php
<?php
// With Ollama client
$ollama->generate([
    'model' => 'llama3.2',
    'prompt' => 'Tell me a story',
    'stream' => true
], function($chunk) {
    if (isset($chunk['response']) echo $chunk['response'];
});

// With OpenAI client
$openai->chatStream('llama3.2', [
    $openai->userMessage('Tell me a story')
], function($chunk) {
    if (isset($chunk['choices'][0]['delta']['content']) echo $chunk['choices'][0]['delta']['content'];
});
```

### Vision Models (Images)

```php
<?php
// With OpenAI client
$response = $openai->chat('llava', [
    $openai->imageMessage(
        'What do you see in this image?',
        'data:image/png;base64,iVBORw0KGg...'
    )
]);
```

### Function Calling (Tools)

```php
<?php
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_weather',
            'description' => 'Get weather information',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'Location'
                    ]
                ],
                'required' => ['location']
            ]
        ]
    ]
];

$response = $openai->chatCompletions([
    'model' => 'llama3.2',
    'messages' => [
        $openai->userMessage('How is the weather in São Paulo?')
    ],
    'tools' => $tools
]);
```

### Advanced Tool System

This library provides a comprehensive tool system for creating and executing custom tools with the `ToolManager` class.

#### Creating Custom Tools

Create tools by implementing the `ToolInterface` or extending `AbstractTool`:

```php
<?php
use Vluzrmos\Ollama\Tools\AbstractTool;

class CalculatorTool extends AbstractTool
{
    public function getName()
    {
        return 'calculator';
    }

    public function getDescription()
    {
        return 'Performs basic mathematical operations';
    }

    public function getParametersSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'operation' => [
                    'type' => 'string',
                    'enum' => ['add', 'subtract', 'multiply', 'divide']
                ],
                'a' => ['type' => 'number'],
                'b' => ['type' => 'number']
            ],
            'required' => ['operation', 'a', 'b']
        ];
    }

    public function execute(array $arguments)
    {
        $a = $arguments['a'];
        $b = $arguments['b'];
        $operation = $arguments['operation'];
        
        switch ($operation) {
            case 'add': return json_encode(['result' => $a + $b]);
            case 'subtract': return json_encode(['result' => $a - $b]);
            case 'multiply': return json_encode(['result' => $a * $b]);
            case 'divide': 
                if ($b == 0) return json_encode(['error' => 'Division by zero']);
                return json_encode(['result' => $a / $b]);
        }
    }
}
```

#### Using Tool Manager

```php
<?php
use Vluzrmos\Ollama\Tools\ToolManager;

// Create and register tools
$toolManager = new ToolManager();
$toolManager->registerTool(new CalculatorTool());

// Make API call with tools
$response = $openai->chatCompletions([
    'model' => 'llama3.2',
    'messages' => [
        ['role' => 'user', 'content' => 'What is 15 + 27?']
    ],
    'tools' => $toolManager->toArray()
]);

// Handle tool calls from response
if (isset($response['choices'][0]['message']['tool_calls'])) {
    $toolCalls = $response['choices'][0]['message']['tool_calls'];
    
    // Execute all tool calls
    $results = $toolManager->executeToolCalls($toolCalls);
    
    // Convert results to message format
    $toolMessages = $toolManager->toolCallResultsToMessages($results);
    
    // Send results back to model
    $messages = [
        ['role' => 'user', 'content' => 'What is 15 + 27?'],
        $response['choices'][0]['message'], // Original response with tool_calls
        ...$toolMessages // Tool results
    ];
    
    $finalResponse = $openai->chatCompletions([
        'model' => 'llama3.2',
        'messages' => $messages
    ]);
    
    echo $finalResponse['choices'][0]['message']['content'];
}
```

#### Tool Call Execution Methods

The `ToolManager` provides several methods for handling tool calls:

- `executeToolCalls($toolCalls)` - Executes multiple tool calls and returns results
- `toolCallResultsToMessages($results)` - Converts tool results to API message format
- `registerTool($tool)` - Registers a new tool
- `listTools()` - Lists all registered tool names
- `getStats()` - Gets statistics about registered tools

#### Error Handling in Tools

```php
<?php
// Tool execution handles errors gracefully
$toolCalls = [
    [
        'id' => 'call_001',
        'type' => 'function',
        'function' => [
            'name' => 'non_existent_tool',
            'arguments' => '{}'
        ]
    ]
];

$results = $toolManager->executeToolCalls($toolCalls);

foreach ($results as $result) {
    if ($result['success']) {
        echo "Tool executed successfully: " . $result['result'];
    } else {
        echo "Tool execution failed: " . $result['error'];
    }
}
```

### JSON Mode

```php
<?php
$response = $openai->chat('llama3.2', [
    $openai->systemMessage('Always respond in valid JSON.'),
    $openai->userMessage('List 3 primary colors')
], [
    'response_format' => $openai->jsonFormat()
]);
```


## JSON Schema

```php
<?php
$response = $openai->chat('llama3.2', [
    'messages' => [
        Message::user('What are the primary colors?')->toArray()
    ],
    'response_format' => [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'primary_colors',
                'description' => 'List of primary colors',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'colors' => [
                            'type' => 'array',
                            'description' => 'List of primary colors in user language',
                            'items' => ['type' => 'string']
                        ]
                    ],
                    'required' => ['colors']
                ],
                
            ]
    ]
]);

echo json_encode($response['choices'][0]['message']['content'], JSON_PRETTY_PRINT);
```

```json
{
    "colors": ["red", "blue", "yellow"]
}
```

> Note: JSON Schema format is useful for validating response structure and ensuring it meets user expectations. Not all models support this format, so check the specific model documentation.

### Embeddings

```php
<?php
// Ollama
$response = $ollama->embeddings([
    'model' => 'all-minilm',
    'input' => 'Text for embedding'
]);

// OpenAI
$response = $openai->embed('all-minilm', [
    'First text',
    'Second text'
]);
```

## OpenAI Compatibility

This library implements the following OpenAI API endpoints:

- ✅ `/v1/chat/completions`
- ✅ `/v1/completions` 
- ✅ `/v1/embeddings`
- ✅ `/v1/models`
- ✅ `/v1/models/{model}`

### Supported Parameters

#### Chat Completions
- `model`, `messages`, `temperature`, `top_p`, `max_tokens`
- `stream`, `stream_options`, `stop`, `seed`
- `frequency_penalty`, `presence_penalty`
- `response_format` (JSON mode: `json_object`, `json_schema`)
- `tools` (function calling)

#### Completions
- `model`, `prompt`, `temperature`, `top_p`, `max_tokens`
- `stream`, `stream_options`, `stop`, `seed`, `suffix`
- `frequency_penalty`, `presence_penalty`

#### Embeddings
- `model`, `input` (string or array)

## Model Management

```php
// List models
$models = $ollama->listModels();

// Download model
$ollama->pullModel(['model' => 'llama3.2']);

// Model information
$info = $ollama->showModel('llama3.2');

// Delete model
$ollama->deleteModel('old-model');
```

## Error Handling

```php
<?php
use Vluzrmos\Ollama\Exceptions\OllamaException;

try {
    $response = $ollama->chat([
        'model' => 'non-existent-model',
        'messages' => [Message::user('Hello')->toArray()]
    ]);
} catch (OllamaException $e) {
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getCode();
}
```

## Configuration

### Client Options

```php
<?php
$ollama = new Ollama('http://localhost:11434', [
    'timeout' => 60,
    'connect_timeout' => 10,
    'verify_ssl' => false
]);

$openai = new OpenAI('http://localhost:11434/v1', 'ollama', [
    'timeout' => 120
]);
```

## Requirements

- PHP >= 5.6.0
- ext-curl
- ext-json

## Complete Examples

See example files in the `examples/` folder:

- [`basic_usage.php`](examples/basic_usage.php) - Basic Ollama API usage
- [`openai_usage.php`](examples/openai_usage.php) - OpenAI API examples
- [`advanced_chat.php`](examples/advanced_chat.php) - Advanced chat with tools
- [`tool_execution_demo.php`](examples/tool_execution_demo.php) - Comprehensive tool system demonstration
- [`simple_tool_test.php`](examples/simple_tool_test.php) - Simple tool execution test

### Tool Examples

Tool implementations can be found in `examples/tools/`:

- [`CalculatorTool.php`](examples/tools/CalculatorTool.php) - Mathematical operations tool
- [`WeatherTool.php`](examples/tools/WeatherTool.php) - Weather information tool (mock)

## Testing
Build the docker image and run tests:

```bash
docker build -t ollama-php56 .
docker run -it --rm \
    -e OPENAI_API_URL="http://localhost:11434/v1" \
    -e OLLAMA_API_URL="http://localhost:11434" \
    -e RUN_INTEGRATION_TESTS=1 \
    -e TEST_MODEL="llama3.2:1b" \
    ollama-php56
```

## License

MIT

## Contributions

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

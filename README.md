# Ollama PHP Client

PHP client for Ollama API, compatible with PHP 5.6+. This library provides an easy-to-use interface to interact with Ollama server and also includes OpenAI API compatibility.

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

use Ollama\Ollama;
use Ollama\Models\Message;
use Ollama\Models\Model;

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
use Ollama\OpenAI;
use Ollama\Models\Model;

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
use Ollama\Models\Model;

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

### JSON Mode

```php
$response = $openai->chat('llama3.2', [
    $openai->systemMessage('Always respond in valid JSON.'),
    $openai->userMessage('List 3 primary colors')
], [
    'response_format' => $openai->jsonFormat()
]);
```


## JSON Schema

```php

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
use Ollama\Exceptions\OllamaException;

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

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributions

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

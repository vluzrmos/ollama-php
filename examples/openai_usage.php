<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/tools/WeatherTool.php';

date_default_timezone_set('America/Bahia');

use Examples\Tools\WeatherTool;
use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Tools\ToolManager;
use Vluzrmos\Ollama\Utils\ImageHelper;

// Configure OpenAI compatible client
$openai = new OpenAI(getenv('OPENAI_API_URL', 'http://localhost:11434/v1'), 'ollama');

$model = new Model('qwen2.5:3b');
$modelReasoning = new Model('qwen3:4b');
$modelVision = new Model('qwen2.5vl:3b');
$modelEmbedding = new Model('all-minilm');

echo "=== OpenAI Example 1: Chat Completions ===\n";
try {
    $openai->chatStream($model, [
        Message::system('You are a helpful assistant that responds in English.'),
        Message::user('Hello, how are you?')
    ], function ($chunk) {
        if (isset($chunk['choices'][0]['delta']['content'])) {
            echo $chunk['choices'][0]['delta']['content'];
        }
    });

    echo "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Example 2: Chat with Model Class ===\n";
try {
    $openai->chatStream($model, [
        Message::system('You are a poet who writes in English.'),
        Message::user('Write a haiku about the ocean'),
    ], function ($chunk) {
        if (isset($chunk['choices'][0]['delta']['content'])) {
            echo $chunk['choices'][0]['delta']['content'];
        }
    });

    echo "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Example 3: Completions ===\n";
try {
    $response = $openai->complete($model, 'What is artificial intelligence?', [
        'max_tokens' => 100,
        'temperature' => 0.7
    ]);

    echo "Response: " . $response['choices'][0]['text'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}
echo "=== OpenAI Example 4: Chat with Image (Llava) ===\n";
try {
    // Example with base64 image (replace with a real image)
    $imageBase64 = ImageHelper::encodeImageUrl(__DIR__ . '/sample-720p.jpg');

    echo "Base64 image: " . substr($imageBase64, 0, 30) . "...\n"; // Just for demonstration

    $response = $openai->chat($modelVision, [
        Message::system('You are an assistant that can see images and respond in English.'),
        Message::image('What do you see in this image?', $imageBase64)
    ]);

    echo "Response: " . $response['choices'][0]['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";

    exit(1);
}


echo "=== OpenAI Example 5: Streaming Chat ===\n";
try {
    echo "Streaming response: ";
    $openai->chatStream($model, [
        Message::user('Tell me a short story')
    ], function ($chunk) {
        if (isset($chunk['choices'][0]['delta']['content'])) {
            echo $chunk['choices'][0]['delta']['content'];
        }
    });
    echo "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Example 6: Embeddings ===\n";
try {
    $response = $openai->embed($modelEmbedding, [
        'Why is the sky blue?',
        'Why is grass green?'
    ]);

    echo "Generated embeddings for " . count($response['data']) . " texts\n";
    echo "Dimensions: " . count($response['data'][0]['embedding']) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Example 7: List Models ===\n";
try {
    $models = $openai->listModels();
    echo "Available models:\n";
    foreach ($models['data'] as $modelInfoItem) {
        echo "- " . $modelInfoItem['id'] . " (owner: " . $modelInfoItem['owned_by'] . ")\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}
echo "=== OpenAI Example 8: Model Information ===\n";
try {
    $modelInfo = $openai->retrieveModel($model);
    echo "Model information:\n";
    echo "- ID: " . $modelInfo['id'] . "\n";
    echo "- Object: " . $modelInfo['object'] . "\n";
    echo "- Owner: " . $modelInfo['owned_by'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Example 9: Chat with JSON Mode ===\n";
try {
    $response = $openai->chat($model, [
        Message::system('You are an assistant that always responds in valid JSON format. Keep the json_schema format provided, do not translate field names.'),
        Message::user('List 3 primary colors')
    ], [
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
        ],
        'temperature' => 0.6
    ]);

    echo  $response['choices'][0]['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Example 10: Tools (Function Calling) ===\n";

$tools = new ToolManager();

$tools->registerTool(new WeatherTool());

// $tools = [
//     [
//         'type' => 'function',
//         'function' => [
//             'name' => 'get_current_weather',
//             'description' => 'Get current weather from a location',
//             'parameters' => [
//                 'type' => 'object',
//                 'properties' => [
//                     'location' => [
//                         'type' => 'string',
//                         'description' => 'The city and state, e.g. São Paulo, SP'
//                     ],
//                     'unit' => [
//                         'type' => 'string',
//                         'enum' => ['celsius', 'fahrenheit']
//                     ]
//                 ],
//                 'required' => ['location']
//             ]
//         ]
//     ]
// ];

$messages = [
    Message::system('You are a helpful assistant that can help with weather information.'),
    Message::user('What is the weather in São Paulo today?')
];

$response = $openai->chatCompletions([
    'model' => $model,
    'messages' => $messages,
    'tools' => $tools->jsonSerialize(),
]);

echo "Response with tools: " . json_encode($response['choices'][0]['message'], JSON_PRETTY_PRINT) . "\n\n";

$messages[] = $response['choices'][0]['message'];

$results = $tools->executeToolCalls($response['choices'][0]['message']['tool_calls']);

$messages = array_merge($messages, $tools->toolCallResultsToMessages($results));

$response = $openai->chatCompletions([
    'model' => $model,
    'messages' => $messages,
]);

echo "Final response: " . $response['choices'][0]['message']['content'] . "\n\n";

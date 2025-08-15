<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OpenAI;
use Ollama\Models\Model;
use Ollama\Utils\ImageHelper;

// Configurar cliente OpenAI compatível
$openai = new OpenAI(getenv('OPENAI_API_URL', 'http://localhost:11434/v1'), 'ollama');

$model = new Model('qwen2.5:3b');
$modelReasoning = new Model('qwen3:4b');
$modelVision = new Model('qwen2.5vl:3b');
$modelEmbedding = new Model('all-minilm');

echo "=== Exemplo OpenAI 1: Chat Completions ===\n";
try {
    $openai->chatStream($model, [
        $openai->systemMessage('Você é um assistente útil que responde em português.'),
        $openai->userMessage('Olá, como você está?')
    ], function ($chunk) {
        if (isset($chunk['choices'][0]['delta']['content'])) {
            echo $chunk['choices'][0]['delta']['content'];
        }
    });

    echo "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo OpenAI 2: Chat com Model Class ===\n";
try {    
    $openai->chatStream($model, [
        $openai->systemMessage('Você é um poeta que escreve em português.'),
        $openai->userMessage('Escreva um haiku sobre o mar'),
    ], function ($chunk) {
        if (isset($chunk['choices'][0]['delta']['content'])) {
            echo $chunk['choices'][0]['delta']['content'];
        }
    });

    echo "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo OpenAI 3: Completions ===\n";
try {
    $response = $openai->complete($model, 'O que é inteligência artificial?', [
        'max_tokens' => 100,
        'temperature' => 0.7
    ]);
    
    echo "Resposta: " . $response['choices'][0]['text'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}
echo "=== Exemplo OpenAI 4: Chat com Imagem (Llava) ===\n";
try {
    // Exemplo com imagem base64 (substitua por uma imagem real)
    $imageBase64 = ImageHelper::encodeImageUrl(__DIR__ . '/sample.png');
    
    echo "Imagem base64: " . substr($imageBase64, 0, 30) . "...\n"; // Apenas para demonstração

    $response = $openai->chat($modelVision, [
        $openai->systemMessage('Você é um assistente que pode ver imagens e responder em português.'),
        $openai->imageMessage('O que você vê nesta imagem?', $imageBase64)
    ]);
    
    echo "Resposta: " . $response['choices'][0]['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}


echo "=== Exemplo OpenAI 5: Streaming Chat ===\n";
try {
    echo "Resposta streaming: ";
    $openai->chatStream($model, [
        $openai->userMessage('Conte uma história curta')
    ], function($chunk) {
        if (isset($chunk['choices'][0]['delta']['content'])) {
            echo $chunk['choices'][0]['delta']['content'];
        }
    });
    echo "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo OpenAI 6: Embeddings ===\n";
try {
    $response = $openai->embed($modelEmbedding, [
        'Por que o céu é azul?',
        'Por que a grama é verde?'
    ]);
    
    echo "Embeddings gerados para " . count($response['data']) . " textos\n";
    echo "Dimensões: " . count($response['data'][0]['embedding']) . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo OpenAI 7: Listar Modelos ===\n";
try {
    $models = $openai->listModels();
    echo "Modelos disponíveis:\n";
    foreach ($models['data'] as $modelInfoItem) {
        echo "- " . $modelInfoItem['id'] . " (owner: " . $modelInfoItem['owned_by'] . ")\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}
echo "=== Exemplo OpenAI 8: Informações de Modelo ===\n";
try {
    $modelInfo = $openai->retrieveModel($model);
    echo "Informações do modelo:\n";
    echo "- ID: " . $modelInfo['id'] . "\n";
    echo "- Objeto: " . $modelInfo['object'] . "\n";
    echo "- Proprietário: " . $modelInfo['owned_by'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo OpenAI 9: Chat com JSON Mode ===\n";
try {
    $response = $openai->chat($model, [
        $openai->systemMessage('Você é um assistente que sempre responde em formato JSON válido. Manter o formato do json_schema fornecido, não traduzir nome dos campos.'),
        $openai->userMessage('Liste 3 cores primárias')
    ], [
        'response_format' => [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'primary_colors',
                'description' => 'Lista de cores primárias',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'colors' => [
                            'type' => 'array',
                            'description' => 'Lista de cores primárias no idioma do usuário',
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
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo OpenAI 10: Tools (Function Calling) ===\n";

try {
    $tools = [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_current_weather',
                'description' => 'Obtém o clima atual de uma localização',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'A cidade e estado, ex: São Paulo, SP'
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit']
                        ]
                    ],
                    'required' => ['location']
                ]
            ]
        ]
    ];
    
    $response = $openai->chatCompletions([
        'model' => $model,
        'messages' => [
            $openai->systemMessage('Você é um assistente útil que pode ajudar com informações sobre o clima.'),
            $openai->userMessage('Qual é o clima em São Paulo hoje?')
        ],
        'tools' => $tools
    ]);
    
    echo "Resposta com tools: " . json_encode($response['choices'][0]['message'], JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

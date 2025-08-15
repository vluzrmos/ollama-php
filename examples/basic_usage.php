<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;
use Ollama\Models\Message;
use Ollama\Models\Tool;
use Ollama\Utils\ImageHelper;

// Configurar cliente
$client = new OllamaClient(getenv('OLLAMA_API_URL') ?: 'http://localhost:11434');

$defaultModel = 'qwen2.5:3b'; // Modelo padrão para os exemplos
echo "=== Exemplo 1: Generate Completion ===\n";
try {
    $response = $client->generate([
        'model' => $defaultModel,
        'prompt' => 'Por que o céu é azul?',
        'stream' => false
    ]);
    
    echo "Resposta: " . $response['response'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 2: Chat Completion ===\n";
try {
    $messages = [
        Message::user('Olá, como você está?'),
    ];
    
    $response = $client->chat([
        'model' => $defaultModel,
        'messages' => array_map(function($msg) { return $msg->toArray(); }, $messages),
        'stream' => false
    ]);
    
    echo "Resposta: " . $response['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 3: Chat com Histórico ===\n";
try {
    $messages = [
        Message::system('Você é um assistente útil que responde em português.'),
        Message::user('Qual é a capital do Brasil?'),
        Message::assistant('A capital do Brasil é Brasília.'),
        Message::user('E qual é a população dessa cidade?')
    ];
    
    $response = $client->chat([
        'model' => $defaultModel,
        'messages' => array_map(function($msg) { return $msg->toArray(); }, $messages),
        'stream' => false
    ]);
    
    echo "Resposta: " . $response['message']['content'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 4: Streaming ===\n";
try {
    echo "Resposta streaming: ";
    $client->generate([
        'model' => $defaultModel,
        'prompt' => 'Conte uma piada curta',
        'stream' => true
    ], function($chunk) {
        if (isset($chunk['response'])) {
            echo $chunk['response'];
        }
    });
    echo "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 5: Tool Calling ===\n";
try {
    $tools = [
        
    ];
    
    $response = $client->chat([
        'model' => $defaultModel,
        'messages' => [
            Message::user('Qual é o clima em São Paulo hoje?')->toArray()
        ],
        'tools' => $tools,
        'stream' => false
    ]);
    
    echo "Tool calls: " . json_encode($response['message'], JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 6: Listar Modelos ===\n";
try {
    $models = $client->listModels();
    echo "Modelos disponíveis:\n";
    foreach ($models['models'] as $model) {
        echo "- " . $model['name'] . " (" . round($model['size'] / 1024 / 1024 / 1024, 2) . " GB)\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 7: Embeddings ===\n";
try {
    $response = $client->embeddings([
        'model' => 'all-minilm',
        'input' => 'Este é um texto para gerar embeddings'
    ]);
    
    echo "Embeddings gerados: " . count($response['embeddings'][0]) . " dimensões\n";
    echo "Primeiras 5 dimensões: " . json_encode(array_slice($response['embeddings'][0], 0, 5)) . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 8: Informações do Modelo ===\n";
try {
    $info = $client->showModel($defaultModel);
    echo "Informações do modelo:\n";
    echo "- Família: " . (isset($info['details']['family']) ? $info['details']['family'] : 'N/A') . "\n";
    echo "- Parâmetros: " . (isset($info['details']['parameter_size']) ? $info['details']['parameter_size'] : 'N/A') . "\n";
    echo "- Formato: " . (isset($info['details']['format']) ? $info['details']['format'] : 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}

echo "=== Exemplo 9: Versão do Ollama ===\n";
try {
    $version = $client->version();
    echo "Versão do Ollama: " . $version['version'] . "\n\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n\n";
}
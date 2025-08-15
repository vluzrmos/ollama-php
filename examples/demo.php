<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;
use Ollama\Models\Message;

echo "=== Demo: Ollama PHP 5.6 Client ===\n\n";

try {
    // Criar cliente
    $client = new OllamaClient('http://localhost:11434');
    
    // Verificar se o servidor está funcionando
    echo "Verificando conexão com Ollama...\n";
    $version = $client->version();
    echo "✅ Conectado! Versão: " . $version['version'] . "\n\n";
    
    // Listar modelos disponíveis
    echo "Modelos disponíveis:\n";
    $models = $client->listModels();
    
    if (empty($models['models'])) {
        echo "⚠️  Nenhum modelo encontrado. Faça o download de um modelo primeiro:\n";
        echo "   ollama pull llama3.2\n\n";
        exit(1);
    }
    
    foreach ($models['models'] as $model) {
        $size = round($model['size'] / 1024 / 1024 / 1024, 2);
        echo "- {$model['name']} ({$size} GB)\n";
    }
    
    // Usar o primeiro modelo disponível
    $modelName = $models['models'][0]['name'];
    echo "\nUsando modelo: $modelName\n\n";
    
    // Teste de completion simples
    echo "=== Teste 1: Completion simples ===\n";
    echo "Pergunta: Por que o céu é azul?\n";
    
    $response = $client->generate([
        'model' => $modelName,
        'prompt' => 'Por que o céu é azul? Responda em português de forma concisa.',
        'stream' => false
    ]);
    
    echo "Resposta: " . trim($response['response']) . "\n\n";
    
    // Teste de chat
    echo "=== Teste 2: Chat ===\n";
    $messages = [
        Message::system('Você é um assistente útil que sempre responde em português.'),
        Message::user('Qual é a capital do Brasil?')
    ];
    
    echo "Sistema: Você é um assistente útil que sempre responde em português.\n";
    echo "Usuário: Qual é a capital do Brasil?\n";
    
    $response = $client->chat([
        'model' => $modelName,
        'messages' => array_map(function($msg) { return $msg->toArray(); }, $messages),
        'stream' => false
    ]);
    
    echo "Assistente: " . trim($response['message']['content']) . "\n\n";
    
    // Teste de embeddings (se houver modelo disponível)
    echo "=== Teste 3: Embeddings ===\n";
    try {
        // Tentar com um modelo comum de embeddings
        $embeddingModels = ['all-minilm', 'nomic-embed-text'];
        $embeddingModel = null;
        
        foreach ($embeddingModels as $model) {
            foreach ($models['models'] as $availableModel) {
                if (strpos($availableModel['name'], $model) !== false) {
                    $embeddingModel = $availableModel['name'];
                    break 2;
                }
            }
        }
        
        if ($embeddingModel) {
            $embResponse = $client->embeddings([
                'model' => $embeddingModel,
                'input' => 'Este é um teste de embeddings em português.'
            ]);
            
            $dimensions = count($embResponse['embeddings'][0]);
            echo "✅ Embeddings gerados com sucesso!\n";
            echo "   Modelo: $embeddingModel\n";
            echo "   Dimensões: $dimensions\n";
            echo "   Primeiros valores: " . implode(', ', array_slice($embResponse['embeddings'][0], 0, 5)) . "...\n\n";
        } else {
            echo "⚠️  Nenhum modelo de embeddings encontrado.\n";
            echo "   Sugestão: ollama pull all-minilm\n\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Erro ao testar embeddings: " . $e->getMessage() . "\n\n";
    }
    
    // Estatísticas finais
    echo "=== Estatísticas ===\n";
    $runningModels = $client->listRunningModels();
    echo "Modelos em memória: " . count($runningModels['models']) . "\n";
    
    foreach ($runningModels['models'] as $model) {
        $vramGB = round($model['size_vram'] / 1024 / 1024 / 1024, 2);
        echo "- {$model['name']}: {$vramGB} GB VRAM\n";
    }
    
    echo "\n✅ Demo concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "\nVerifique se:\n";
    echo "1. O Ollama está instalado e em execução\n";
    echo "2. O servidor está acessível em http://localhost:11434\n";
    echo "3. Pelo menos um modelo foi baixado (ollama pull llama3.2)\n";
    exit(1);
}

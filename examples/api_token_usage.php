<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;
use Ollama\Models\Message;

echo "=== Exemplo: Usando Ollama com Token de API ===\n\n";

// Exemplo 1: Configurando token no construtor
echo "=== Método 1: Token no construtor ===\n";
$client1 = new OllamaClient('http://localhost:11434', array(
    'api_token' => 'seu-token-aqui'
));

echo "Token configurado: " . ($client1->getApiToken() ? "✅" : "❌") . "\n\n";

// Exemplo 2: Configurando token depois da criação
echo "=== Método 2: Token após criação ===\n";
$client2 = new OllamaClient('http://localhost:11434');
$client2->setApiToken('seu-token-aqui');

echo "Token configurado: " . ($client2->getApiToken() ? "✅" : "❌") . "\n\n";

// Exemplo 3: Uso com OpenAI API (compatível)
echo "=== Método 3: Compatibilidade com OpenAI ===\n";
$openaiClient = new OllamaClient('https://api.openai.com/v1', array(
    'api_token' => 'sk-sua-chave-openai-aqui',
    'verify_ssl' => true
));

echo "Cliente OpenAI configurado: " . ($openaiClient->getApiToken() ? "✅" : "❌") . "\n";

// Exemplo de uso com OpenAI (descomente para testar com chave real)
/*
try {
    $response = $openaiClient->chat([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            Message::user('Olá! Como você está?')->toArray()
        ],
        'stream' => false
    ]);
    
    echo "Resposta OpenAI: " . $response['message']['content'] . "\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
*/

echo "\n";

// Exemplo 4: Uso com Ollama local que requer autenticação
echo "=== Método 4: Ollama com autenticação ===\n";
$secureOllamaClient = new OllamaClient('https://meu-ollama-server.com', array(
    'api_token' => 'meu-token-ollama',
    'verify_ssl' => true,
    'timeout' => 120
));

echo "Ollama seguro configurado: " . ($secureOllamaClient->getApiToken() ? "✅" : "❌") . "\n\n";

// Exemplo de diferentes cenários de uso
echo "=== Cenários de Uso ===\n";

echo "1. Ollama local sem autenticação:\n";
echo "   \$client = new OllamaClient('http://localhost:11434');\n\n";

echo "2. Ollama com token personalizado:\n";
echo "   \$client = new OllamaClient('http://localhost:11434', ['api_token' => 'token']);\n\n";

echo "3. OpenAI API:\n";
echo "   \$client = new OllamaClient('https://api.openai.com/v1', ['api_token' => 'sk-...']);\n\n";

echo "4. Outros provedores compatíveis (Anthropic, etc.):\n";
echo "   \$client = new OllamaClient('https://api.anthropic.com', ['api_token' => 'sk-...']);\n\n";

echo "5. Configuração dinâmica de token:\n";
echo "   \$client = new OllamaClient('http://localhost:11434');\n";
echo "   \$client->setApiToken(getenv('OLLAMA_API_KEY'));\n\n";

// Exemplo prático: Função para detectar tipo de API automaticamente
function createClientForProvider($provider, $token = null)
{
    $configs = array(
        'ollama' => array(
            'url' => 'http://localhost:11434',
            'token_required' => false
        ),
        'openai' => array(
            'url' => 'https://api.openai.com/v1',
            'token_required' => true
        ),
        'anthropic' => array(
            'url' => 'https://api.anthropic.com/v1',
            'token_required' => true
        )
    );
    
    if (!isset($configs[$provider])) {
        throw new InvalidArgumentException("Provider '$provider' não suportado");
    }
    
    $config = $configs[$provider];
    $options = array();
    
    if ($config['token_required'] && !$token) {
        throw new InvalidArgumentException("Token é obrigatório para o provider '$provider'");
    }
    
    if ($token) {
        $options['api_token'] = $token;
    }
    
    return new OllamaClient($config['url'], $options);
}

echo "=== Função Helper para Múltiplos Provedores ===\n";

try {
    // Ollama local
    $ollamaClient = createClientForProvider('ollama');
    echo "✅ Cliente Ollama criado\n";
    
    // OpenAI (precisa de token)
    try {
        $openaiClient = createClientForProvider('openai', 'sk-fake-token');
        echo "✅ Cliente OpenAI criado\n";
    } catch (Exception $e) {
        echo "⚠️  " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== Exemplo concluído! ===\n";
echo "O pacote agora suporta:\n";
echo "- Ollama local (com ou sem token)\n";
echo "- OpenAI API\n";
echo "- Qualquer API compatível com OpenAI\n";
echo "- Configuração flexível de autenticação\n";

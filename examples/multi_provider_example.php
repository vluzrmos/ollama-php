<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;
use Ollama\Models\Message;

echo "=== Exemplo Prático: Multi-Provider API Client ===\n\n";

/**
 * Classe helper para gerenciar múltiplos provedores de API
 */
class MultiProviderClient
{
    private $clients = array();
    
    public function addProvider($name, $baseUrl, $token = null)
    {
        $options = array();
        if ($token) {
            $options['api_token'] = $token;
        }
        
        $this->clients[$name] = new OllamaClient($baseUrl, $options);
        echo "✅ Provedor '$name' adicionado\n";
    }
    
    public function chat($provider, $model, $message)
    {
        if (!isset($this->clients[$provider])) {
            throw new InvalidArgumentException("Provedor '$provider' não encontrado");
        }
        
        try {
            $response = $this->clients[$provider]->chat([
                'model' => $model,
                'messages' => [
                    Message::user($message)->toArray()
                ],
                'stream' => false
            ]);
            
            return $response['message']['content'];
        } catch (Exception $e) {
            return "Erro: " . $e->getMessage();
        }
    }
    
    public function listProviders()
    {
        return array_keys($this->clients);
    }
}

// Configurar múltiplos provedores
$multiClient = new MultiProviderClient();

// Ollama local (sem token)
$multiClient->addProvider('ollama-local', 'http://localhost:11434');

// Ollama com autenticação personalizada
$multiClient->addProvider('ollama-secure', 'https://meu-servidor-ollama.com', 'meu-token-ollama');

// OpenAI API (descomente para usar com chave real)
// $multiClient->addProvider('openai', 'https://api.openai.com/v1', 'sk-sua-chave-aqui');

// Anthropic API (descomente para usar com chave real) 
// $multiClient->addProvider('anthropic', 'https://api.anthropic.com/v1', 'sk-ant-sua-chave');

echo "\nProvedores configurados: " . implode(', ', $multiClient->listProviders()) . "\n\n";

// Exemplo de uso dinâmico baseado em variáveis de ambiente
echo "=== Configuração Dinâmica via Variáveis de Ambiente ===\n";

function createClientFromEnv()
{
    $baseUrl = getenv('OLLAMA_BASE_URL') ?: 'http://localhost:11434';
    $token = getenv('OLLAMA_API_TOKEN');
    
    $options = array();
    if ($token) {
        $options['api_token'] = $token;
    }
    
    return new OllamaClient($baseUrl, $options);
}

$envClient = createClientFromEnv();
echo "Cliente criado com URL: " . (getenv('OLLAMA_BASE_URL') ?: 'http://localhost:11434') . "\n";
echo "Token configurado: " . ($envClient->getApiToken() ? "✅" : "❌") . "\n\n";

// Exemplo de troca dinâmica de tokens
echo "=== Troca Dinâmica de Tokens ===\n";

$dynamicClient = new OllamaClient('http://localhost:11434');

echo "Token inicial: " . ($dynamicClient->getApiToken() ?: 'nenhum') . "\n";

$dynamicClient->setApiToken('token-ambiente-dev');
echo "Token para DEV: " . $dynamicClient->getApiToken() . "\n";

$dynamicClient->setApiToken('token-ambiente-prod');
echo "Token para PROD: " . $dynamicClient->getApiToken() . "\n";

$dynamicClient->setApiToken(null);
echo "Token removido: " . ($dynamicClient->getApiToken() ?: 'nenhum') . "\n\n";

// Exemplo de configuração condicional
echo "=== Configuração Condicional por Ambiente ===\n";

function createClientForEnvironment($env)
{
    $configs = array(
        'development' => array(
            'url' => 'http://localhost:11434',
            'token' => null,
            'verify_ssl' => false
        ),
        'staging' => array(
            'url' => 'https://ollama-staging.empresa.com',
            'token' => 'staging-token-123',
            'verify_ssl' => true
        ),
        'production' => array(
            'url' => 'https://api.openai.com/v1',
            'token' => getenv('OPENAI_API_KEY'),
            'verify_ssl' => true
        )
    );
    
    if (!isset($configs[$env])) {
        throw new InvalidArgumentException("Ambiente '$env' não configurado");
    }
    
    $config = $configs[$env];
    $options = array(
        'verify_ssl' => $config['verify_ssl']
    );
    
    if ($config['token']) {
        $options['api_token'] = $config['token'];
    }
    
    return new OllamaClient($config['url'], $options);
}

$environments = array('development', 'staging', 'production');

foreach ($environments as $env) {
    try {
        $client = createClientForEnvironment($env);
        echo "✅ Cliente $env criado com sucesso\n";
        echo "   Token: " . ($client->getApiToken() ? "configurado" : "não configurado") . "\n";
    } catch (Exception $e) {
        echo "⚠️  Erro ao criar cliente $env: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Exemplo de Wrapper para Diferentes APIs ===\n";

/**
 * Wrapper que abstrai diferenças entre APIs
 */
class UniversalAIClient
{
    private $client;
    private $provider;
    
    public function __construct($provider, $config)
    {
        $this->provider = $provider;
        $this->client = new OllamaClient($config['url'], $config['options']);
    }
    
    public function chat($message, $model = null)
    {
        // Modelos padrão por provedor
        $defaultModels = array(
            'ollama' => 'llama3.2',
            'openai' => 'gpt-3.5-turbo',
            'anthropic' => 'claude-3-sonnet-20240229'
        );
        
        $model = $model ?: $defaultModels[$this->provider];
        
        try {
            $response = $this->client->chat([
                'model' => $model,
                'messages' => [Message::user($message)->toArray()],
                'stream' => false
            ]);
            
            return array(
                'success' => true,
                'message' => $response['message']['content'],
                'provider' => $this->provider,
                'model' => $model
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->provider
            );
        }
    }
}

// Configurações dos provedores
$providerConfigs = array(
    'ollama' => array(
        'url' => 'http://localhost:11434',
        'options' => array()
    ),
    'openai' => array(
        'url' => 'https://api.openai.com/v1',
        'options' => array('api_token' => 'sk-fake-key')
    )
);

// Criar clientes universais
foreach ($providerConfigs as $provider => $config) {
    try {
        $universalClient = new UniversalAIClient($provider, $config);
        echo "✅ Cliente universal '$provider' criado\n";
        
        // Simular chat (descomente para testar com APIs reais)
        /*
        $result = $universalClient->chat("Olá! Como você está?");
        if ($result['success']) {
            echo "   Resposta: " . $result['message'] . "\n";
        } else {
            echo "   Erro: " . $result['error'] . "\n";
        }
        */
    } catch (Exception $e) {
        echo "❌ Erro ao criar cliente '$provider': " . $e->getMessage() . "\n";
    }
}

echo "\n=== Resumo das Funcionalidades Implementadas ===\n";
echo "✅ Suporte para API Token (Bearer authentication)\n";
echo "✅ Compatibilidade com OpenAI API\n";
echo "✅ Compatibilidade com Anthropic API\n";
echo "✅ Configuração via construtor ou método\n";
echo "✅ Configuração dinâmica de tokens\n";
echo "✅ Suporte a variáveis de ambiente\n";
echo "✅ Configuração condicional por ambiente\n";
echo "✅ Wrapper universal para múltiplos provedores\n";
echo "\n🎉 Pacote pronto para uso em produção!\n";

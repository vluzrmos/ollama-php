<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;

/**
 * Classe para carregar configurações e criar clientes
 */
class OllamaConfigManager
{
    private $config;
    private $environment;
    
    public function __construct($configPath = null, $environment = 'development')
    {
        $configPath = $configPath ?: __DIR__ . '/../config/config.php';
        
        if (!file_exists($configPath)) {
            throw new RuntimeException("Arquivo de configuração não encontrado: $configPath");
        }
        
        $this->config = require $configPath;
        $this->environment = $environment;
    }
    
    public function createClient($provider = null)
    {
        // Usar provedor padrão se não especificado
        if (!$provider) {
            $envConfig = $this->config['environments'][$this->environment];
            $provider = $envConfig['provider'];
        }
        
        if (!isset($this->config['providers'][$provider])) {
            throw new InvalidArgumentException("Provedor '$provider' não encontrado na configuração");
        }
        
        $providerConfig = $this->config['providers'][$provider];
        
        return new OllamaClient($providerConfig['url'], $providerConfig['options']);
    }
    
    public function getDefaultModel($type = 'chat')
    {
        return isset($this->config['default_models'][$type]) 
            ? $this->config['default_models'][$type] 
            : null;
    }
    
    public function getEnvironmentConfig()
    {
        return isset($this->config['environments'][$this->environment])
            ? $this->config['environments'][$this->environment]
            : array();
    }
    
    public function listProviders()
    {
        return array_keys($this->config['providers']);
    }
}

// Exemplo de uso
echo "=== Exemplo de Uso com ConfigManager ===\n\n";

try {
    // Criar gerenciador de configuração
    $configManager = new OllamaConfigManager(
        __DIR__ . '/../config/config.example.php', 
        'development'
    );
    
    echo "✅ ConfigManager criado para ambiente: development\n";
    
    // Listar provedores disponíveis
    $providers = $configManager->listProviders();
    echo "Provedores disponíveis: " . implode(', ', $providers) . "\n\n";
    
    // Criar cliente padrão
    $client = $configManager->createClient();
    echo "✅ Cliente padrão criado\n";
    
    // Criar cliente específico
    $ollamaClient = $configManager->createClient('ollama-local');
    echo "✅ Cliente Ollama local criado\n";
    
    // Obter modelo padrão
    $defaultModel = $configManager->getDefaultModel('chat');
    echo "Modelo padrão para chat: $defaultModel\n";
    
    // Obter configuração do ambiente
    $envConfig = $configManager->getEnvironmentConfig();
    echo "Debug habilitado: " . ($envConfig['debug'] ? 'sim' : 'não') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "💡 Dica: Copie config.example.php para config.php e ajuste as configurações\n";
}

echo "\n=== Exemplo de Uso Simplificado ===\n";

// Função helper para criar cliente facilmente
function createOllamaClient($environment = 'development', $provider = null)
{
    static $configManager = null;
    
    if ($configManager === null) {
        $configPath = __DIR__ . '/../config/config.php';
        if (!file_exists($configPath)) {
            $configPath = __DIR__ . '/../config/config.example.php';
        }
        
        $configManager = new OllamaConfigManager($configPath, $environment);
    }
    
    return $configManager->createClient($provider);
}

// Exemplos de uso simplificado
try {
    $devClient = createOllamaClient('development');
    echo "✅ Cliente de desenvolvimento criado\n";
    
    $openaiClient = createOllamaClient('development', 'openai');
    echo "✅ Cliente OpenAI criado\n";
    
} catch (Exception $e) {
    echo "⚠️ " . $e->getMessage() . "\n";
}

echo "\n✨ Sistema de configuração implementado com sucesso!\n";

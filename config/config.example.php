<?php

/**
 * Arquivo de configuração de exemplo para o Ollama PHP Client
 * 
 * Copie este arquivo para config.php e ajuste conforme necessário
 */

return array(
    // Configuração padrão
    'default' => array(
        'provider' => 'ollama-local',
    ),
    
    // Configurações de provedores
    'providers' => array(
        
        // Ollama local sem autenticação
        'ollama-local' => array(
            'url' => 'http://localhost:11434',
            'options' => array(
                'timeout' => 60,
                'verify_ssl' => false
            )
        ),
        
        // Ollama com autenticação personalizada
        'ollama-secure' => array(
            'url' => 'https://meu-ollama-server.com',
            'options' => array(
                'api_token' => getenv('OLLAMA_API_TOKEN'),
                'timeout' => 120,
                'verify_ssl' => true
            )
        ),
        
        // OpenAI API
        'openai' => array(
            'url' => 'https://api.openai.com/v1',
            'options' => array(
                'api_token' => getenv('OPENAI_API_KEY'),
                'timeout' => 60,
                'verify_ssl' => true
            ),
            'models' => array(
                'chat' => 'gpt-3.5-turbo',
                'completion' => 'gpt-3.5-turbo-instruct',
                'embedding' => 'text-embedding-3-small'
            )
        ),
        
        // Anthropic API
        'anthropic' => array(
            'url' => 'https://api.anthropic.com/v1',
            'options' => array(
                'api_token' => getenv('ANTHROPIC_API_KEY'),
                'timeout' => 60,
                'verify_ssl' => true
            ),
            'models' => array(
                'chat' => 'claude-3-sonnet-20240229'
            )
        ),
        
        // Configuração para desenvolvimento
        'development' => array(
            'url' => 'http://localhost:11434',
            'options' => array(
                'timeout' => 30,
                'verify_ssl' => false
            )
        ),
        
        // Configuração para staging
        'staging' => array(
            'url' => 'https://staging-api.empresa.com',
            'options' => array(
                'api_token' => getenv('STAGING_API_TOKEN'),
                'timeout' => 90,
                'verify_ssl' => true
            )
        ),
        
        // Configuração para produção
        'production' => array(
            'url' => 'https://api.empresa.com',
            'options' => array(
                'api_token' => getenv('PRODUCTION_API_TOKEN'),
                'timeout' => 120,
                'verify_ssl' => true
            )
        )
    ),
    
    // Modelos padrão por tipo
    'default_models' => array(
        'chat' => 'llama3.2',
        'completion' => 'llama3.2',
        'embedding' => 'all-minilm'
    ),
    
    // Configurações por ambiente
    'environments' => array(
        'development' => array(
            'provider' => 'development',
            'debug' => true,
            'log_requests' => true
        ),
        'staging' => array(
            'provider' => 'staging',
            'debug' => false,
            'log_requests' => true
        ),
        'production' => array(
            'provider' => 'production',
            'debug' => false,
            'log_requests' => false
        )
    )
);

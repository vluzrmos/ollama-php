<?php

// Bootstrap para os testes
// Configura o autoloader e variáveis de ambiente padrão

// Autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Define timezone padrão
date_default_timezone_set('America/Bahia');

// Configurações padrão de ambiente para testes
if (!getenv('OLLAMA_API_URL')) {
    putenv('OLLAMA_API_URL=http://localhost:11434');
}

if (!getenv('OPENAI_API_URL')) {
    putenv('OPENAI_API_URL=http://localhost:11434/v1');
}

if (!getenv('TEST_MODEL')) {
    putenv('TEST_MODEL=llama3.2:1b');
}

if (!getenv('RUN_INTEGRATION_TESTS')) {
    putenv('RUN_INTEGRATION_TESTS=0');
}

// Configurações do PHP para testes
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Função auxiliar para debug durante desenvolvimento dos testes
if (!function_exists('test_debug')) {
    function test_debug($message, $data = null) {
        if (getenv('TEST_DEBUG') === '1') {
            echo "[DEBUG] " . $message;
            if ($data !== null) {
                echo ": " . print_r($data, true);
            }
            echo "\n";
        }
    }
}

// Função auxiliar para verificar se temos conectividade com o servidor
if (!function_exists('check_server_connectivity')) {
    function check_server_connectivity($url, $timeout = 5) {
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => $timeout,
                'method' => 'GET'
            )
        ));
        
        $result = @file_get_contents($url, false, $context);
        return $result !== false;
    }
}

echo "Bootstrap concluído.\n";
echo "OLLAMA_API_URL: " . getenv('OLLAMA_API_URL') . "\n";
echo "OPENAI_API_URL: " . getenv('OPENAI_API_URL') . "\n";
echo "TEST_MODEL: " . getenv('TEST_MODEL') . "\n";
echo "RUN_INTEGRATION_TESTS: " . getenv('RUN_INTEGRATION_TESTS') . "\n";

<?php

/**
 * Teste simples para o sistema de tools (compatível com PHP 5.6)
 * Execute: php tests/simple_tools_test.php
 */

require_once __DIR__ . '/../src/Tools/ToolInterface.php';
require_once __DIR__ . '/../src/Tools/AbstractTool.php';
require_once __DIR__ . '/../src/Tools/ToolManager.php';
require_once __DIR__ . '/../src/Tools/WeatherTool.php';
require_once __DIR__ . '/../src/Tools/CalculatorTool.php';
require_once __DIR__ . '/../src/Tools/WebSearchTool.php';
require_once __DIR__ . '/../src/Tools/DateTimeTool.php';

use Ollama\Tools\ToolManager;
use Ollama\Tools\WeatherTool;
use Ollama\Tools\CalculatorTool;

class SimpleToolsTest
{
    private $toolManager;
    private $testsPassed = 0;
    private $testsFailed = 0;

    public function __construct()
    {
        $this->toolManager = new ToolManager();
    }

    public function run()
    {
        echo "=== Teste Simples do Sistema de Tools ===\n\n";

        $this->testToolManagerInitialization();
        $this->testDefaultToolsRegistration();
        $this->testCalculatorOperations();
        $this->testWeatherTool();
        $this->testCustomTool();
        $this->testToolManagerMethods();

        echo "\n=== Resultado dos Testes ===\n";
        echo "Testes passaram: {$this->testsPassed}\n";
        echo "Testes falharam: {$this->testsFailed}\n";

        if ($this->testsFailed === 0) {
            echo "✅ Todos os testes passaram!\n";
        } else {
            echo "❌ Alguns testes falharam.\n";
        }
    }

    private function assert($condition, $message)
    {
        if ($condition) {
            $this->testsPassed++;
            echo "✅ {$message}\n";
        } else {
            $this->testsFailed++;
            echo "❌ {$message}\n";
        }
    }

    private function testToolManagerInitialization()
    {
        echo "1. Testando inicialização do ToolManager...\n";
        
        $tools = $this->toolManager->listTools();
        $this->assert(empty($tools), "ToolManager inicia vazio");
        $this->assert(count($this->toolManager->getToolsForAPI()) === 0, "Nenhuma tool na API inicialmente");
    }

    private function testDefaultToolsRegistration()
    {
        echo "\n2. Testando registro de tools padrão...\n";
        
        $this->toolManager->registerDefaultTools();
        $tools = $this->toolManager->listTools();
        
        $this->assert(in_array('get_weather', $tools), "WeatherTool registrada");
        $this->assert(in_array('calculator', $tools), "CalculatorTool registrada");
        $this->assert(in_array('web_search', $tools), "WebSearchTool registrada");
        $this->assert(in_array('datetime_operations', $tools), "DateTimeTool registrada");
        $this->assert(count($tools) === 4, "4 tools padrão registradas");
    }

    private function testCalculatorOperations()
    {
        echo "\n3. Testando operações da CalculatorTool...\n";
        
        // Teste adição
        $result = json_decode($this->toolManager->executeTool('calculator', array(
            'operation' => 'add',
            'a' => 10,
            'b' => 5
        )), true);
        $this->assert($result['result'] == 15, "Adição 10 + 5 = 15");
        
        // Teste multiplicação
        $result = json_decode($this->toolManager->executeTool('calculator', array(
            'operation' => 'multiply',
            'a' => 6,
            'b' => 7
        )), true);
        $this->assert($result['result'] == 42, "Multiplicação 6 × 7 = 42");
        
        // Teste divisão por zero
        $result = json_decode($this->toolManager->executeTool('calculator', array(
            'operation' => 'divide',
            'a' => 10,
            'b' => 0
        )), true);
        $this->assert(isset($result['error']), "Divisão por zero retorna erro");
    }

    private function testWeatherTool()
    {
        echo "\n4. Testando WeatherTool...\n";
        
        $result = json_decode($this->toolManager->executeTool('get_weather', array(
            'city' => 'São Paulo',
            'format' => 'celsius'
        )), true);
        
        $this->assert($result['city'] === 'São Paulo', "Cidade retornada corretamente");
        $this->assert(isset($result['temperature']), "Temperatura presente na resposta");
        $this->assert(isset($result['condition']), "Condição climática presente");
        
        // Teste sem parâmetros obrigatórios
        $result = json_decode($this->toolManager->executeTool('get_weather', array()), true);
        $this->assert(isset($result['error']), "Erro quando cidade não informada");
    }

    private function testCustomTool()
    {
        echo "\n5. Testando tool personalizada...\n";
        
        // Criar tool personalizada inline para PHP 5.6
        $customTool = new CustomTestTool();
        $this->toolManager->registerTool($customTool);
        
        $this->assert($this->toolManager->hasTool('reverse_string'), "Tool personalizada registrada");
        
        $result = json_decode($this->toolManager->executeTool('reverse_string', array(
            'text' => 'hello'
        )), true);
        
        $this->assert($result['reversed'] === 'olleh', "String invertida corretamente");
        $this->assert($result['original'] === 'hello', "String original preservada");
    }

    private function testToolManagerMethods()
    {
        echo "\n6. Testando métodos do ToolManager...\n";
        
        // Teste de estatísticas
        $stats = $this->toolManager->getStats();
        $this->assert($stats['total_tools'] > 0, "Estatísticas contém tools registradas");
        $this->assert(isset($stats['tools']), "Estatísticas contém detalhes das tools");
        
        // Teste de tool inexistente
        $result = json_decode($this->toolManager->executeTool('nonexistent_tool', array()), true);
        $this->assert(isset($result['error']), "Tool inexistente retorna erro");
        
        // Teste de remoção de tool
        $removed = $this->toolManager->unregisterTool('reverse_string');
        $this->assert($removed === true, "Tool removida com sucesso");
        $this->assert(!$this->toolManager->hasTool('reverse_string'), "Tool não existe após remoção");
    }
}

// Tool personalizada para teste
class CustomTestTool extends Ollama\Tools\AbstractTool
{
    public function getName()
    {
        return 'reverse_string';
    }

    public function getDescription()
    {
        return 'Inverte uma string';
    }

    public function getParametersSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'text' => array(
                    'type' => 'string',
                    'description' => 'Texto para inverter'
                )
            ),
            'required' => array('text')
        );
    }

    public function execute(array $arguments)
    {
        $text = isset($arguments['text']) ? $arguments['text'] : '';
        
        if (empty($text)) {
            return json_encode(array('error' => 'Texto é obrigatório'));
        }
        
        return json_encode(array(
            'original' => $text,
            'reversed' => strrev($text),
            'length' => strlen($text)
        ));
    }
}

// Executar os testes
$test = new SimpleToolsTest();
$test->run();

<?php

use PHPUnit\Framework\TestCase;
use Ollama\Tools\ToolManager;
use Ollama\Tools\WeatherTool;
use Ollama\Tools\CalculatorTool;
use Ollama\Tools\AbstractTool;

class ToolSystemTest extends TestCase
{
    /**
     * @var ToolManager
     */
    private $toolManager;

    protected function setUp()
    {
        $this->toolManager = new ToolManager();
    }

    public function testToolManagerInitialization()
    {
        $this->assertEmpty($this->toolManager->listTools());
        $this->assertEquals(0, count($this->toolManager->jsonSerialize()));
    }

    public function testDefaultToolsRegistration()
    {
        $this->toolManager->registerDefaultTools();
        
        $tools = $this->toolManager->listTools();
        $this->assertContains('get_weather', $tools);
        $this->assertContains('calculator', $tools);
        $this->assertContains('web_search', $tools);
        $this->assertContains('datetime_operations', $tools);
        $this->assertEquals(4, count($tools));
    }

    public function testIndividualToolRegistration()
    {
        $weatherTool = new WeatherTool();
        $this->toolManager->registerTool($weatherTool);
        
        $this->assertTrue($this->toolManager->hasTool('get_weather'));
        $this->assertInstanceOf('Ollama\\Tools\\WeatherTool', $this->toolManager->getTool('get_weather'));
    }

    public function testToolExecution()
    {
        $calculatorTool = new CalculatorTool();
        $this->toolManager->registerTool($calculatorTool);
        
        $result = $this->toolManager->executeTool('calculator', array(
            'operation' => 'add',
            'a' => 10,
            'b' => 5
        ));
        
        $decoded = json_decode($result, true);
        $this->assertEquals('add', $decoded['operation']);
        $this->assertEquals(15, $decoded['result']);
    }

    public function testToolExecutionWithInvalidTool()
    {
        $result = $this->toolManager->executeTool('nonexistent_tool', array());
        
        $decoded = json_decode($result, true);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertContains('Tool não encontrada', $decoded['error']);
    }

    public function testCalculatorToolOperations()
    {
        $calculator = new CalculatorTool();
        
        // Teste adição
        $result = json_decode($calculator->execute(array(
            'operation' => 'add',
            'a' => 5,
            'b' => 3
        )), true);
        $this->assertEquals(8, $result['result']);
        
        // Teste subtração
        $result = json_decode($calculator->execute(array(
            'operation' => 'subtract',
            'a' => 10,
            'b' => 4
        )), true);
        $this->assertEquals(6, $result['result']);
        
        // Teste multiplicação
        $result = json_decode($calculator->execute(array(
            'operation' => 'multiply',
            'a' => 6,
            'b' => 7
        )), true);
        $this->assertEquals(42, $result['result']);
        
        // Teste divisão
        $result = json_decode($calculator->execute(array(
            'operation' => 'divide',
            'a' => 20,
            'b' => 4
        )), true);
        $this->assertEquals(5, $result['result']);
        
        // Teste divisão por zero
        $result = json_decode($calculator->execute(array(
            'operation' => 'divide',
            'a' => 10,
            'b' => 0
        )), true);
        $this->assertArrayHasKey('error', $result);
    }

    public function testWeatherToolExecution()
    {
        $weather = new WeatherTool();
        
        // Teste com parâmetros válidos
        $result = json_decode($weather->execute(array(
            'city' => 'São Paulo',
            'format' => 'celsius'
        )), true);
        
        $this->assertEquals('São Paulo', $result['city']);
        $this->assertArrayHasKey('temperature', $result);
        $this->assertArrayHasKey('condition', $result);
        $this->assertArrayHasKey('humidity', $result);
        
        // Teste sem cidade
        $result = json_decode($weather->execute(array()), true);
        $this->assertArrayHasKey('error', $result);
    }

    public function testToolToArrayFormat()
    {
        $calculator = new CalculatorTool();
        $array = $calculator->toArray();
        
        $this->assertEquals('function', $array['type']);
        $this->assertEquals('calculator', $array['function']['name']);
        $this->assertArrayHasKey('description', $array['function']);
        $this->assertArrayHasKey('parameters', $array['function']);
        
        $params = $array['function']['parameters'];
        $this->assertEquals('object', $params['type']);
        $this->assertArrayHasKey('properties', $params);
        $this->assertArrayHasKey('required', $params);
    }

    public function testCustomToolImplementation()
    {
        // Criar tool personalizada para teste
        $customTool = new class extends AbstractTool {
            public function getName()
            {
                return 'test_tool';
            }

            public function getDescription()
            {
                return 'Tool de teste';
            }

            public function getParametersSchema()
            {
                return array(
                    'type' => 'object',
                    'properties' => array(
                        'input' => array(
                            'type' => 'string',
                            'description' => 'Input para teste'
                        )
                    ),
                    'required' => array('input')
                );
            }

            public function execute(array $arguments)
            {
                $input = isset($arguments['input']) ? $arguments['input'] : '';
                return json_encode(array(
                    'processed' => strtoupper($input),
                    'length' => strlen($input)
                ));
            }
        };

        $this->toolManager->registerTool($customTool);
        
        $result = json_decode($this->toolManager->executeTool('test_tool', array(
            'input' => 'hello world'
        )), true);
        
        $this->assertEquals('HELLO WORLD', $result['processed']);
        $this->assertEquals(11, $result['length']);
    }

    public function testToolUnregistration()
    {
        $this->toolManager->registerTool(new WeatherTool());
        $this->assertTrue($this->toolManager->hasTool('get_weather'));
        
        $this->assertTrue($this->toolManager->unregisterTool('get_weather'));
        $this->assertFalse($this->toolManager->hasTool('get_weather'));
        
        $this->assertFalse($this->toolManager->unregisterTool('nonexistent_tool'));
    }

    public function testToolManagerStats()
    {
        $this->toolManager->registerDefaultTools();
        
        $stats = $this->toolManager->getStats();
        $this->assertEquals(4, $stats['total_tools']);
        $this->assertArrayHasKey('tools', $stats);
        
        foreach ($stats['tools'] as $toolName => $toolInfo) {
            $this->assertArrayHasKey('name', $toolInfo);
            $this->assertArrayHasKey('description', $toolInfo);
            $this->assertArrayHasKey('parameters_count', $toolInfo);
        }
    }
}

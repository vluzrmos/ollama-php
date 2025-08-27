<?php

use Vluzrmos\Ollama\Tools\ToolManager;
use Vluzrmos\Ollama\Tools\ToolInterface;
use Vluzrmos\Ollama\Tools\ToolCallResult;
use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

class MockTool implements ToolInterface
{
    private $name;
    private $description;
    private $parameters;
    
    public function __construct($name = 'test_tool', $description = 'A test tool', $parameters = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->parameters = $parameters;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getParametersSchema()
    {
        return $this->parameters;
    }

    public function toArray()
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => $this->getParametersSchema()
            ]
        ];
    }

    public function execute(array $arguments)
    {
        if (isset($arguments['fail']) && $arguments['fail']) {
            throw new ToolExecutionException('Test execution failure');
        }
        
        return 'Executed with arguments: ' . json_encode($arguments);
    }

    public function jsonSerialize()
    {
        $data = $this->toArray();
        
        if (isset($data['function']['parameters'])) {
            $parameters = $data['function']['parameters'];
            $required = isset($parameters['required']) ? $parameters['required'] : [];
            
            $parameters['required'] = (object) $required;
            $data['function']['parameters'] = (object) $parameters;
        }
        
        return $data;
    }
}

class ToolManagerTest extends TestCase
{
    private $toolManager;

    public function setUp()
    {
        $this->toolManager = new ToolManager();
    }

    public function testToolManagerConstruction()
    {
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Tools\\ToolManager', $this->toolManager);
    }

    public function testRegisterTool()
    {
        $tool = new MockTool();
        
        $this->toolManager->registerTool($tool);
        
        $this->assertTrue($this->toolManager->hasTool('test_tool'));
        $this->assertSame($tool, $this->toolManager->getTool('test_tool'));
    }

    public function testGetNonExistentTool()
    {
        $tool = $this->toolManager->getTool('nonexistent');
        
        $this->assertNull($tool);
    }

    public function testUnregisterTool()
    {
        $tool = new MockTool();
        $this->toolManager->registerTool($tool);
        
        $result = $this->toolManager->unregisterTool('test_tool');
        
        $this->assertTrue($result);
        $this->assertFalse($this->toolManager->hasTool('test_tool'));
        $this->assertNull($this->toolManager->getTool('test_tool'));
    }

    public function testUnregisterNonExistentTool()
    {
        $result = $this->toolManager->unregisterTool('nonexistent');
        
        $this->assertFalse($result);
    }

    public function testListTools()
    {
        $tool1 = new MockTool('tool1');
        $tool2 = new MockTool('tool2');
        
        $this->toolManager->registerTool($tool1);
        $this->toolManager->registerTool($tool2);
        
        $tools = $this->toolManager->listTools();
        
        $this->assertTrue(is_array($tools));
        $this->assertCount(2, $tools);
        $this->assertContains('tool1', $tools);
        $this->assertContains('tool2', $tools);
    }

    public function testListToolsEmpty()
    {
        $tools = $this->toolManager->listTools();
        
        $this->assertTrue(is_array($tools));
        $this->assertEmpty($tools);
    }

    public function testToArray()
    {
        $tool1 = new MockTool('tool1', 'First tool');
        $tool2 = new MockTool('tool2', 'Second tool');
        
        $this->toolManager->registerTool($tool1);
        $this->toolManager->registerTool($tool2);
        
        $array = $this->toolManager->toArray();
        
        $this->assertTrue(is_array($array));
        $this->assertCount(2, $array);
        
        foreach ($array as $toolArray) {
            $this->assertArrayHasKey('type', $toolArray);
            $this->assertArrayHasKey('function', $toolArray);
            $this->assertEquals('function', $toolArray['type']);
        }
    }

    public function testJsonSerialize()
    {
        $tool = new MockTool();
        $this->toolManager->registerTool($tool);
        
        $json = json_encode($this->toolManager);
        
        $this->assertNotEmpty($json);
        
        $decoded = json_decode($json, true);
        $this->assertTrue(is_array($decoded));
        $this->assertCount(1, $decoded);
    }

    public function testExecuteTool()
    {
        $tool = new MockTool();
        $this->toolManager->registerTool($tool);
        
        $result = $this->toolManager->executeTool('test_tool', ['arg1' => 'value1']);
        
        $this->assertTrue(is_string($result));
        $this->assertContains('value1', $result);
    }

    public function testExecuteNonExistentTool()
    {
        $result = $this->toolManager->executeTool('nonexistent', []);

        $this->assertNull($result);
    }

    public function testHasTool()
    {
        $tool = new MockTool();
        
        $this->assertFalse($this->toolManager->hasTool('test_tool'));
        
        $this->toolManager->registerTool($tool);
        
        $this->assertTrue($this->toolManager->hasTool('test_tool'));
    }

    public function testExecuteToolCalls()
    {
        $tool = new MockTool();
        $this->toolManager->registerTool($tool);
        
        $toolCalls = [
            [
                'id' => 'call_123',
                'function' => [
                    'name' => 'test_tool',
                    'arguments' => '{"arg1": "value1"}'
                ]
            ]
        ];
        
        $results = $this->toolManager->executeToolCalls($toolCalls);
        
        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        
        $result = $results[0];
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Tools\\ToolCallResult', $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('test_tool', $result->getToolName());
        $this->assertEquals('call_123', $result->getToolCallId());
    }

    public function testExecuteToolCallsWithInvalidStructure()
    {
        $toolCalls = [
            ['invalid' => 'structure']
        ];
        
        $results = $this->toolManager->executeToolCalls($toolCalls);
        
        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        
        $result = $results[0];
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Tools\\ToolCallResult', $result);
        $this->assertFalse($result->isSuccess());
        $this->assertContains('Invalid tool call', $result->getErrorMessage());
    }

    public function testExecuteToolCallsWithNonExistentTool()
    {
        $toolCalls = [
            [
                'id' => 'call_456',
                'function' => [
                    'name' => 'nonexistent_tool',
                    'arguments' => '{}'
                ]
            ]
        ];
        
        $results = $this->toolManager->executeToolCalls($toolCalls);
        
        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        
        $result = $results[0];
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Tools\\ToolCallResult', $result);
        $this->assertFalse($result->isSuccess());
        $this->assertContains('wasn\'t found', $result->getErrorMessage());
    }

    public function testExecuteToolCallsWithFailingTool()
    {
        $tool = new MockTool();
        $this->toolManager->registerTool($tool);
        
        $toolCalls = [
            [
                'id' => 'call_789',
                'function' => [
                    'name' => 'test_tool',
                    'arguments' => '{"fail": true}'
                ]
            ]
        ];
        
        $results = $this->toolManager->executeToolCalls($toolCalls);
        
        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        
        $result = $results[0];
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Tools\\ToolCallResult', $result);
        $this->assertFalse($result->isSuccess());
        $this->assertContains('Test execution failure', $result->getErrorMessage());
    }

    public function testDecodeToolCallArgumentsWithValidJson()
    {
        $arguments = '{"key": "value", "number": 42}';
        
        $decoded = $this->toolManager->decodeToolCallArguments($arguments);
        
        $this->assertTrue(is_array($decoded));
        $this->assertEquals('value', $decoded['key']);
        $this->assertEquals(42, $decoded['number']);
    }

    public function testDecodeToolCallArgumentsWithArray()
    {
        $arguments = ['key' => 'value'];
        
        $decoded = $this->toolManager->decodeToolCallArguments($arguments);
        
        $this->assertTrue(is_array($decoded));
        $this->assertEquals('value', $decoded['key']);
    }

    public function testDecodeToolCallArgumentsWithEmptyInput()
    {
        $decoded = $this->toolManager->decodeToolCallArguments(null);
        
        $this->assertTrue(is_array($decoded));
        $this->assertEmpty($decoded);
    }

    public function testDecodeToolCallArgumentsWithInvalidJson()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid JSON arguments');
        
        $this->toolManager->decodeToolCallArguments('invalid json');
    }

    public function testToolCallResultsToMessages()
    {
        $results = [
            new ToolCallResult('tool1', 'result1', true, null, 'call_1'),
            new ToolCallResult('tool2', null, false, 'error message', 'call_2')
        ];
        
        $messages = $this->toolManager->toolCallResultsToMessages($results);
        
        $this->assertTrue(is_array($messages));
        $this->assertCount(2, $messages);
        
        foreach ($messages as $message) {
            $this->assertInstanceOf('Vluzrmos\\Ollama\\Models\\Message', $message);
            $this->assertEquals('tool', $message->role);
        }
    }

    public function testGetStats()
    {
        $tool1 = new MockTool('tool1', 'First tool', [
            'properties' => ['arg1' => ['type' => 'string']]
        ]);
        $tool2 = new MockTool('tool2', 'Second tool', [
            'properties' => ['arg1' => ['type' => 'string'], 'arg2' => ['type' => 'number']]
        ]);
        
        $this->toolManager->registerTool($tool1);
        $this->toolManager->registerTool($tool2);
        
        $stats = $this->toolManager->getStats();
        
        $this->assertTrue(is_array($stats));
        $this->assertArrayHasKey('total_tools', $stats);
        $this->assertArrayHasKey('tools', $stats);
        $this->assertEquals(2, $stats['total_tools']);
        
        $this->assertTrue(is_array($stats['tools']));
        $this->assertCount(2, $stats['tools']);
        
        $this->assertArrayHasKey('tool1', $stats['tools']);
        $this->assertArrayHasKey('tool2', $stats['tools']);
        
        $this->assertEquals(1, $stats['tools']['tool1']['parameters_count']);
        $this->assertEquals(2, $stats['tools']['tool2']['parameters_count']);
    }

    public function testGetStatsEmpty()
    {
        $stats = $this->toolManager->getStats();
        
        $this->assertTrue(is_array($stats));
        $this->assertEquals(0, $stats['total_tools']);
        $this->assertTrue(is_array($stats['tools']));
        $this->assertEmpty($stats['tools']);
    }
}

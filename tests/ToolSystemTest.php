<?php

use PHPUnit\Framework\TestCase;
use Ollama\Tools\ToolManager;

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

    public function testToolExecutionWithInvalidTool()
    {
        $result = $this->toolManager->executeTool('nonexistent_tool', array());
        
        $decoded = json_decode($result, true);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertContains('Tool não encontrada', $decoded['error']);
    }
}

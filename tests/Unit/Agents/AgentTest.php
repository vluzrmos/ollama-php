<?php

namespace Vluzrmos\Ollama\Tests\Unit\Agents;

use PHPUnit\Framework\TestCase;
use Vluzrmos\Ollama\Agents\Agent;
use Vluzrmos\Ollama\Agents\AgentGroup;
use Vluzrmos\Ollama\Agents\ClientAdapterInterface;
use Vluzrmos\Ollama\Tools\TimeTool;

class AgentTest extends TestCase
{
    private $mockAdapter;

    protected function setUp()
    {
        $this->mockAdapter = $this->createMock(ClientAdapterInterface::class);
    }

    public function testAgentCreation()
    {
        $agent = new Agent(
            'TestAgent',
            $this->mockAdapter,
            'test-model',
            'Test instructions',
            'Test description',
            [new TimeTool()],
            ['temperature' => 0.5]
        );

        $this->assertEquals('TestAgent', $agent->getName());
        $this->assertEquals('Test description', $agent->getDescription());
        $this->assertEquals('Test instructions', $agent->getInstructions());
        $this->assertEquals('test-model', $agent->getModel());
        $this->assertInstanceOf('Vluzrmos\Ollama\Tools\ToolManager', $agent->getTools());
        $this->assertCount(1, $agent->getTools()->listTools());
        $this->assertEquals(['temperature' => 0.5], $agent->getOptions());
    }

    public function testAgentCanHandle()
    {
        $agent = new Agent(
            'TestAgent',
            $this->mockAdapter,
            'test-model',
            'Test instructions'
        );

        // Default implementation should always return true
        $this->assertTrue($agent->canHandle('Any message'));
    }

    public function testAgentToolManagement()
    {
        $agent = new Agent(
            'TestAgent',
            $this->mockAdapter,
            'test-model',
            'Test instructions'
        );

        $this->assertCount(0, $agent->getTools()->listTools());

        $tool = new TimeTool();
        $agent->addTool($tool);
        $this->assertCount(1, $agent->getTools()->listTools());

        $removed = $agent->removeTool('get_current_time');
        $this->assertTrue($removed);
        $this->assertCount(0, $agent->getTools()->listTools());

        $removed = $agent->removeTool('nonexistent');
        $this->assertFalse($removed);
    }

    public function testAgentGroupCreation()
    {
        $agent1 = new Agent('Agent1', $this->mockAdapter, 'model1', 'Instructions1');
        $agent2 = new Agent('Agent2', $this->mockAdapter, 'model2', 'Instructions2');

        $group = new AgentGroup(
            'TestGroup',
            $this->mockAdapter,
            'coordinator-model',
            [$agent1, $agent2],
            'Test group description'
        );

        $this->assertEquals('TestGroup', $group->getName());
        $this->assertEquals('Test group description', $group->getDescription());
        $this->assertCount(2, $group->getAgents());
        $this->assertInstanceOf('Vluzrmos\Ollama\Tools\ToolManager', $group->getTools());
        $this->assertTrue($group->canHandle('Any message'));
    }

    public function testAgentGroupManagement()
    {
        $group = new AgentGroup(
            'TestGroup',
            $this->mockAdapter,
            'coordinator-model'
        );

        $agent = new Agent('TestAgent', $this->mockAdapter, 'test-model', 'Test instructions');

        $this->assertCount(0, $group->getAgents());

        $group->addAgent($agent);
        $this->assertCount(1, $group->getAgents());

        $retrievedAgent = $group->getAgent('TestAgent');
        $this->assertSame($agent, $retrievedAgent);

        $nonExistentAgent = $group->getAgent('NonExistent');
        $this->assertNull($nonExistentAgent);

        $removed = $group->removeAgent('TestAgent');
        $this->assertTrue($removed);
        $this->assertCount(0, $group->getAgents());

        $removed = $group->removeAgent('NonExistent');
        $this->assertFalse($removed);
    }

    public function testAgentGroupInstructions()
    {
        $agent1 = new Agent('MathExpert', $this->mockAdapter, 'model1', 'Math instructions', 'Math specialist');
        $agent2 = new Agent('Writer', $this->mockAdapter, 'model2', 'Writing instructions', 'Creative writer');

        $group = new AgentGroup(
            'TestGroup',
            $this->mockAdapter,
            'coordinator-model',
            [$agent1, $agent2]
        );

        $instructions = $group->getInstructions();
        
        // Check that agent list is included in instructions
        $this->assertContains('MathExpert', $instructions);
        $this->assertContains('Writer', $instructions);
        $this->assertContains('Math specialist', $instructions);
        $this->assertContains('Creative writer', $instructions);
    }
}
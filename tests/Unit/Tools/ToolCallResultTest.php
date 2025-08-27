<?php

use Vluzrmos\Ollama\Tools\ToolCallResult;
use Vluzrmos\Ollama\Models\Message;

class ToolCallResultTest extends TestCase
{
    public function testToolCallResultConstruction()
    {
        $result = new ToolCallResult('test_tool', 'result data', true);
        
        $this->assertEquals('test_tool', $result->getToolName());
        $this->assertEquals('result data', $result->getResult());
        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->getErrorMessage());
        $this->assertNull($result->getToolCallId());
    }

    public function testToolCallResultConstructionWithAllParameters()
    {
        $result = new ToolCallResult(
            'calculator',
            '42',
            true,
            null,
            'call_123'
        );
        
        $this->assertEquals('calculator', $result->getToolName());
        $this->assertEquals('42', $result->getResult());
        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->getErrorMessage());
        $this->assertEquals('call_123', $result->getToolCallId());
    }

    public function testToolCallResultConstructionWithError()
    {
        $result = new ToolCallResult(
            'failing_tool',
            null,
            false,
            'Tool execution failed',
            'call_456'
        );
        
        $this->assertEquals('failing_tool', $result->getToolName());
        $this->assertNull($result->getResult());
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Tool execution failed', $result->getErrorMessage());
        $this->assertEquals('call_456', $result->getToolCallId());
    }

    public function testSetters()
    {
        $result = new ToolCallResult('tool', 'data');
        
        $returned = $result->setToolCallId('new_call_id');
        $this->assertSame($result, $returned); // Test fluent interface
        $this->assertEquals('new_call_id', $result->getToolCallId());
        
        $returned = $result->setToolName('new_tool_name');
        $this->assertSame($result, $returned);
        $this->assertEquals('new_tool_name', $result->getToolName());
        
        $returned = $result->setResult('new_result');
        $this->assertSame($result, $returned);
        $this->assertEquals('new_result', $result->getResult());
        
        $returned = $result->setSuccess(false);
        $this->assertSame($result, $returned);
        $this->assertFalse($result->isSuccess());
        
        $returned = $result->setErrorMessage('New error message');
        $this->assertSame($result, $returned);
        $this->assertEquals('New error message', $result->getErrorMessage());
    }

    public function testGetResultString()
    {
        // Test with string result
        $result = new ToolCallResult('tool', 'string result');
        $this->assertEquals('string result', $result->getResultString());
        
        // Test with array result
        $arrayResult = ['key' => 'value', 'number' => 42];
        $result = new ToolCallResult('tool', $arrayResult);
        $this->assertEquals(json_encode($arrayResult), $result->getResultString());
        
        // Test with null result
        $result = new ToolCallResult('tool', null);
        $this->assertEquals('null', $result->getResultString());
    }

    public function testGetMessageContentString()
    {
        // Test successful result
        $result = new ToolCallResult('tool', 'success result', true);
        $this->assertEquals('success result', $result->getMessageContentString());
        
        // Test failed result with error message
        $result = new ToolCallResult('tool', null, false, 'Error occurred');
        $this->assertEquals('Error occurred', $result->getMessageContentString());
        
        // Test failed result without error message
        $result = new ToolCallResult('tool', null, false, null);
        $this->assertEquals('', $result->getMessageContentString());
    }

    public function testToMessage()
    {
        $result = new ToolCallResult('calculator', '42', true, null, 'call_123');
        
        $message = $result->toMessage();
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Models\\Message', $message);
        $this->assertEquals('tool', $message->role);
        $this->assertEquals('42', $message->content);
        $this->assertEquals('call_123', $message->toolCallId);
        $this->assertEquals('calculator', $message->toolName);
    }

    public function testToMessageWithCustomRole()
    {
        $result = new ToolCallResult('calculator', '42', true, null, 'call_123');
        
        $message = $result->toMessage('assistant');
        
        $this->assertEquals('assistant', $message->role);
        $this->assertEquals('42', $message->content);
        $this->assertEquals('call_123', $message->toolCallId);
        $this->assertEquals('calculator', $message->toolName);
    }

    public function testToMessageWithFailedResult()
    {
        $result = new ToolCallResult('failing_tool', null, false, 'Execution failed', 'call_456');
        
        $message = $result->toMessage();
        
        $this->assertEquals('tool', $message->role);
        $this->assertEquals('Execution failed', $message->content);
        $this->assertEquals('call_456', $message->toolCallId);
        $this->assertEquals('failing_tool', $message->toolName);
    }

    public function testToMessageWithArrayResult()
    {
        $arrayResult = ['status' => 'complete', 'data' => [1, 2, 3]];
        $result = new ToolCallResult('data_tool', $arrayResult, true, null, 'call_789');
        
        $message = $result->toMessage();
        
        $this->assertEquals(json_encode($arrayResult), $message->content);
    }

    public function testFluentInterface()
    {
        $result = new ToolCallResult('tool', 'data');
        
        $returned = $result
            ->setToolCallId('call_999')
            ->setToolName('updated_tool')
            ->setResult('updated_result')
            ->setSuccess(false)
            ->setErrorMessage('Updated error');
        
        $this->assertSame($result, $returned);
        $this->assertEquals('call_999', $result->getToolCallId());
        $this->assertEquals('updated_tool', $result->getToolName());
        $this->assertEquals('updated_result', $result->getResult());
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Updated error', $result->getErrorMessage());
    }
}

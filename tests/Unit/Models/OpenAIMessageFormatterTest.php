<?php

use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Models\OpenAIMessageFormatter;

class OpenAIMessageFormatterTest extends TestCase
{
    private $formatter;

    public function setUp()
    {
        $this->formatter = new OpenAIMessageFormatter();
    }

    public function testFormatBasicMessage()
    {
        $message = new Message('user', 'Hello world');
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'user',
            'content' => 'Hello world'
        ], $formatted);
    }

    public function testFormatMessageWithImages()
    {
        $images = ['data:image/jpeg;base64,abc123', 'data:image/png;base64,def456'];
        $message = new Message('user', 'Look at these images', $images);
        
        $formatted = $this->formatter->format($message);
        
        $expectedContent = [
            [
                'type' => 'text',
                'text' => 'Look at these images'
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:image/jpeg;base64,abc123'
                ]
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:image/png;base64,def456'
                ]
            ]
        ];
        
        $this->assertEquals([
            'role' => 'user',
            'content' => $expectedContent
        ], $formatted);
    }

    public function testFormatMessageWithToolCalls()
    {
        $message = new Message('assistant', 'I need to use a tool');
        $toolCalls = [
            [
                'id' => 'call_123',
                'type' => 'function',
                'function' => [
                    'name' => 'calculator',
                    'arguments' => '{"a": 5, "b": 3}'
                ]
            ]
        ];
        $message->toolCalls = $toolCalls;
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'assistant',
            'content' => 'I need to use a tool',
            'tool_calls' => $toolCalls
        ], $formatted);
    }

    public function testFormatMessageWithToolName()
    {
        $message = new Message('tool', 'Result: 8');
        $message->toolName = 'calculator';
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'tool',
            'content' => 'Result: 8',
            'tool_name' => 'calculator'
        ], $formatted);
    }

    public function testFormatMessageWithToolCallId()
    {
        $message = new Message('tool', 'Result: 8');
        $message->toolCallId = 'call_123';
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'tool',
            'content' => 'Result: 8',
            'tool_call_id' => 'call_123'
        ], $formatted);
    }

    public function testFormatMessageWithThinking()
    {
        $message = new Message('assistant', 'The answer is 8');
        $message->thinking = 'I need to calculate 5 + 3';
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'assistant',
            'content' => 'The answer is 8',
            'thinking' => 'I need to calculate 5 + 3'
        ], $formatted);
    }

    public function testFormatComplexMessage()
    {
        $message = new Message('assistant', 'Here is the analysis');
        $message->images = ['data:image/jpeg;base64,abc123'];
        $message->thinking = 'Analyzing the image...';
        $message->toolCalls = [
            [
                'id' => 'call_456',
                'type' => 'function',
                'function' => [
                    'name' => 'image_analyzer',
                    'arguments' => '{}'
                ]
            ]
        ];
        
        $formatted = $this->formatter->format($message);
        
        $expectedContent = [
            [
                'type' => 'text',
                'text' => 'Here is the analysis'
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:image/jpeg;base64,abc123'
                ]
            ]
        ];
        
        $expected = [
            'role' => 'assistant',
            'content' => $expectedContent,
            'thinking' => 'Analyzing the image...',
            'tool_calls' => [
                [
                    'id' => 'call_456',
                    'type' => 'function',
                    'function' => [
                        'name' => 'image_analyzer',
                        'arguments' => '{}'
                    ]
                ]
            ]
        ];
        
        $this->assertEquals($expected, $formatted);
    }

    public function testFormatMessageWithEmptyImages()
    {
        $message = new Message('user', 'No images here');
        $message->images = [];
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'user',
            'content' => 'No images here'
        ], $formatted);
    }

    public function testFormatMessageWithNullFields()
    {
        $message = new Message('user', 'Simple message');
        $message->toolCalls = null;
        $message->toolName = null;
        $message->thinking = null;
        $message->toolCallId = null;
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'user',
            'content' => 'Simple message'
        ], $formatted);
    }

    public function testFormatMessageWithEmptyToolCallId()
    {
        $message = new Message('tool', 'Tool result');
        $message->toolCallId = '';
        
        $formatted = $this->formatter->format($message);
        
        $this->assertEquals([
            'role' => 'tool',
            'content' => 'Tool result'
        ], $formatted);
    }
}

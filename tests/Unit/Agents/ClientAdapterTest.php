<?php

namespace Vluzrmos\Ollama\Tests\Unit\Agents;

use PHPUnit\Framework\TestCase;
use Vluzrmos\Ollama\Agents\OpenAIClientAdapter;
use Vluzrmos\Ollama\Agents\OllamaClientAdapter;
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Ollama;

class ClientAdapterTest extends TestCase
{
    public function testOpenAIClientAdapter()
    {
        $openaiClient = $this->createMock(OpenAI::class);
        $adapter = new OpenAIClientAdapter($openaiClient);

        $this->assertEquals('openai', $adapter->getClientType());
        $this->assertSame($openaiClient, $adapter->getClient());
    }

    public function testOllamaClientAdapter()
    {
        $ollamaClient = $this->createMock(Ollama::class);
        $adapter = new OllamaClientAdapter($ollamaClient);

        $this->assertEquals('ollama', $adapter->getClientType());
        $this->assertSame($ollamaClient, $adapter->getClient());
    }

    public function testOpenAIAdapterChatCompletion()
    {
        $openaiClient = $this->createMock(OpenAI::class);
        $adapter = new OpenAIClientAdapter($openaiClient);

        $model = 'test-model';
        $messages = [
            ['role' => 'user', 'content' => 'Hello']
        ];
        $tools = [];
        $options = ['temperature' => 0.7];

        $expectedParams = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7
        ];

        $openaiClient->expects($this->once())
            ->method('chatCompletions')
            ->with($expectedParams)
            ->willReturn(['response' => 'test']);

        $result = $adapter->chatCompletion($model, $messages, $tools, $options);
        $this->assertEquals(['response' => 'test'], $result);
    }

    public function testOpenAIAdapterChatCompletionWithTools()
    {
        $openaiClient = $this->createMock(OpenAI::class);
        $adapter = new OpenAIClientAdapter($openaiClient);

        $model = 'test-model';
        $messages = [
            ['role' => 'user', 'content' => 'Hello']
        ];
        $tools = [
            ['type' => 'function', 'function' => ['name' => 'test_tool']]
        ];
        $options = ['temperature' => 0.7];

        $expectedParams = [
            'model' => $model,
            'messages' => $messages,
            'tools' => $tools,
            'temperature' => 0.7
        ];

        $openaiClient->expects($this->once())
            ->method('chatCompletions')
            ->with($expectedParams)
            ->willReturn(['response' => 'test']);

        $result = $adapter->chatCompletion($model, $messages, $tools, $options);
        $this->assertEquals(['response' => 'test'], $result);
    }

    public function testOllamaAdapterChatCompletion()
    {
        $ollamaClient = $this->createMock(Ollama::class);
        $adapter = new OllamaClientAdapter($ollamaClient);

        $model = 'test-model';
        $messages = [
            ['role' => 'user', 'content' => 'Hello']
        ];
        $tools = [];
        $options = ['temperature' => 0.7];

        $expectedParams = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7
        ];

        $ollamaClient->expects($this->once())
            ->method('chat')
            ->with($expectedParams)
            ->willReturn(['response' => 'test']);

        $result = $adapter->chatCompletion($model, $messages, $tools, $options);
        $this->assertEquals(['response' => 'test'], $result);
    }
}
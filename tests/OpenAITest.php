<?php

namespace Ollama\Tests;

use PHPUnit_Framework_TestCase;
use Ollama\OpenAI;

class OpenAITest extends PHPUnit_Framework_TestCase
{
    private $openai;

    public function setUp()
    {
        $this->openai = new OpenAI('http://localhost:11434', 'test-token');
    }

    public function testConstructor()
    {
        $this->assertEquals('test-token', $this->openai->getApiKey());
    }

    public function testSetApiKey()
    {
        $this->openai->setApiKey('new-token');
        $this->assertEquals('new-token', $this->openai->getApiKey());
    }

    public function testCreateMessage()
    {
        $message = $this->openai->createMessage('user', 'Hello');
        
        $expected = array(
            'role' => 'user',
            'content' => 'Hello'
        );
        
        $this->assertEquals($expected, $message);
    }

    public function testSystemMessage()
    {
        $message = $this->openai->systemMessage('You are a helpful assistant.');
        
        $expected = array(
            'role' => 'system',
            'content' => 'You are a helpful assistant.'
        );
        
        $this->assertEquals($expected, $message);
    }

    public function testUserMessage()
    {
        $message = $this->openai->userMessage('Hello');
        
        $expected = array(
            'role' => 'user',
            'content' => 'Hello'
        );
        
        $this->assertEquals($expected, $message);
    }

    public function testAssistantMessage()
    {
        $message = $this->openai->assistantMessage('Hi there!');
        
        $expected = array(
            'role' => 'assistant',
            'content' => 'Hi there!'
        );
        
        $this->assertEquals($expected, $message);
    }

    public function testImageMessage()
    {
        $message = $this->openai->imageMessage('What do you see?', 'data:image/png;base64,abc123');
        
        $this->assertEquals('user', $message['role']);
        $this->assertArrayHasKey('content', $message);
        $this->assertTrue(is_array($message['content']));
        $this->assertEquals('text', $message['content'][0]['type']);
        $this->assertEquals('What do you see?', $message['content'][0]['text']);
        $this->assertEquals('image_url', $message['content'][1]['type']);
    }

    public function testJsonFormat()
    {
        $format = $this->openai->jsonFormat();
        
        $expected = array('type' => 'json_object');
        
        $this->assertEquals($expected, $format);
    }

    public function testStreamOptions()
    {
        $options = $this->openai->streamOptions();
        $this->assertEquals(array('include_usage' => false), $options);
        
        $optionsWithUsage = $this->openai->streamOptions(true);
        $this->assertEquals(array('include_usage' => true), $optionsWithUsage);
    }

    public function testChatCompletionsValidation()
    {
        try {
            $this->openai->chatCompletions(array());
            $this->fail('Expected exception for missing model parameter');
        } catch (Exception $e) {
            $this->assertContains('model', $e->getMessage());
        }

        try {
            $this->openai->chatCompletions(array('model' => 'test'));
            $this->fail('Expected exception for missing messages parameter');
        } catch (Exception $e) {
            $this->assertContains('messages', $e->getMessage());
        }
    }

    public function testCompletionsValidation()
    {
        try {
            $this->openai->completions(array());
            $this->fail('Expected exception for missing model parameter');
        } catch (Exception $e) {
            $this->assertContains('model', $e->getMessage());
        }

        try {
            $this->openai->completions(array('model' => 'test'));
            $this->fail('Expected exception for missing prompt parameter');
        } catch (Exception $e) {
            $this->assertContains('prompt', $e->getMessage());
        }
    }

    public function testEmbeddingsValidation()
    {
        try {
            $this->openai->embeddings(array());
            $this->fail('Expected exception for missing model parameter');
        } catch (Exception $e) {
            $this->assertContains('model', $e->getMessage());
        }

        try {
            $this->openai->embeddings(array('model' => 'test'));
            $this->fail('Expected exception for missing input parameter');
        } catch (Exception $e) {
            $this->assertContains('input', $e->getMessage());
        }
    }

    public function testImageMessageWithArray()
    {
        $imageUrl = array('url' => 'data:image/png;base64,abc123');
        $message = $this->openai->imageMessage('What do you see?', $imageUrl);
        
        $this->assertEquals($imageUrl, $message['content'][1]['image_url']);
    }

    public function testImageMessageWithRole()
    {
        $message = $this->openai->imageMessage('What do you see?', 'data:image/png;base64,abc123', 'assistant');
        
        $this->assertEquals('assistant', $message['role']);
    }
}

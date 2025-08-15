<?php

require_once __DIR__ . '/../TestCase.php';

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Exceptions\OllamaException;

class OpenAITest extends TestCase
{
    public function testOpenAICreation()
    {
        $client = new OpenAI();
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\OpenAI', $client);
        $this->assertEquals('ollama', $client->getApiKey());
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Http\\HttpClient', $client->getHttpClient());
    }

    public function testOpenAICreationWithParameters()
    {
        $baseUrl = 'http://test.com:11434/v1';
        $apiKey = 'test-key';
        $options = array('timeout' => 60);
        
        $client = new OpenAI($baseUrl, $apiKey, $options);
        
        $this->assertEquals('test-key', $client->getApiKey());
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Http\\HttpClient', $client->getHttpClient());
    }

    public function testDefaultBaseUrl()
    {
        $client = new OpenAI();
        
        // Verifica se o client HTTP foi criado - indiretamente testa a URL padrão
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Http\\HttpClient', $client->getHttpClient());
    }

    public function testSetAndGetApiKey()
    {
        $client = new OpenAI();
        
        $this->assertEquals('ollama', $client->getApiKey());
        
        $client->setApiKey('new-key');
        $this->assertEquals('new-key', $client->getApiKey());
    }

    public function testChatCompletionsRequiresModel()
    {
        $client = new OpenAI();
        
        $this->expectException('Vluzrmos\\Ollama\\Exceptions\\OllamaException');
        $this->expectExceptionMessage('O parâmetro "model" é obrigatório');
        
        $client->chatCompletions(array(
            'messages' => array(array('role' => 'user', 'content' => 'Hello'))
        ));
    }

    public function testChatCompletionsRequiresMessages()
    {
        $client = new OpenAI();
        
        $this->expectException('Vluzrmos\\Ollama\\Exceptions\\OllamaException');
        $this->expectExceptionMessage('O parâmetro "messages" é obrigatório');
        
        $client->chatCompletions(array(
            'model' => 'llama3.2:1b'
        ));
    }

    public function testCompletionsRequiresModel()
    {
        $client = new OpenAI();
        
        $this->expectException('Vluzrmos\\Ollama\\Exceptions\\OllamaException');
        $this->expectExceptionMessage('O parâmetro "model" é obrigatório');
        
        $client->completions(array(
            'prompt' => 'Hello'
        ));
    }

    public function testCompletionsRequiresPrompt()
    {
        $client = new OpenAI();
        
        $this->expectException('Vluzrmos\\Ollama\\Exceptions\\OllamaException');
        $this->expectExceptionMessage('O parâmetro "prompt" é obrigatório');
        
        $client->completions(array(
            'model' => 'llama3.2:1b'
        ));
    }

    public function testEmbeddingsRequiresModel()
    {
        $client = new OpenAI();
        
        $this->expectException('Vluzrmos\\Ollama\\Exceptions\\OllamaException');
        $this->expectExceptionMessage('O parâmetro "model" é obrigatório');
        
        $client->embeddings(array(
            'input' => 'Hello world'
        ));
    }

    public function testEmbeddingsRequiresInput()
    {
        $client = new OpenAI();
        
        $this->expectException('Vluzrmos\\Ollama\\Exceptions\\OllamaException');
        $this->expectExceptionMessage('O parâmetro "input" é obrigatório');
        
        $client->embeddings(array(
            'model' => 'llama3.2:1b'
        ));
    }

    public function testChatWithStringModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/chat/completions'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 isset($params['messages']) &&
                                 count($params['messages']) === 1;
                      })
                  )
                  ->willReturn($this->createChatCompletionResponse());

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $messages = array(array('role' => 'user', 'content' => 'Hello'));
        $result = $client->chat('llama3.2:1b', $messages);

        $this->assertArrayHasKeys(array('id', 'object', 'created', 'model', 'choices'), $result);
    }

    public function testChatWithModelObject()
    {
        $mockClient = $this->createHttpClientMock();
        $model = new Model('llama3.2:1b');
        $model->setTemperature(0.7);
        
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/chat/completions'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 $params['temperature'] === 0.7;
                      })
                  )
                  ->willReturn($this->createChatCompletionResponse());

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $messages = array(array('role' => 'user', 'content' => 'Hello'));
        $result = $client->chat($model, $messages);

        $this->assertArrayHasKeys(array('id', 'object', 'created', 'model', 'choices'), $result);
    }

    public function testCompleteWithStringModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/completions'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 $params['prompt'] === 'Hello';
                      })
                  )
                  ->willReturn($this->createCompletionResponse());

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->complete('llama3.2:1b', 'Hello');

        $this->assertArrayHasKeys(array('id', 'object', 'created', 'model', 'choices'), $result);
    }

    public function testEmbedWithStringModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/embeddings'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 $params['input'] === 'Hello world';
                      })
                  )
                  ->willReturn($this->createEmbeddingResponse());

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->embed('llama3.2:1b', 'Hello world');

        $this->assertArrayHasKeys(array('object', 'data', 'model', 'usage'), $result);
    }

    public function testCreateMessage()
    {
        $client = new OpenAI();
        
        $message = $client->createMessage('user', 'Hello');
        
        $this->assertEquals(array(
            'role' => 'user',
            'content' => 'Hello'
        ), $message);
    }

    public function testSystemMessage()
    {
        $client = new OpenAI();
        
        $message = $client->systemMessage('You are a helpful assistant');
        
        $this->assertEquals(array(
            'role' => 'system',
            'content' => 'You are a helpful assistant'
        ), $message);
    }

    public function testUserMessage()
    {
        $client = new OpenAI();
        
        $message = $client->userMessage('Hello');
        
        $this->assertEquals(array(
            'role' => 'user',
            'content' => 'Hello'
        ), $message);
    }

    public function testAssistantMessage()
    {
        $client = new OpenAI();
        
        $message = $client->assistantMessage('Hi there!');
        
        $this->assertEquals(array(
            'role' => 'assistant',
            'content' => 'Hi there!'
        ), $message);
    }

    public function testImageMessage()
    {
        $client = new OpenAI();
        
        $message = $client->imageMessage('What is in this image?', 'data:image/jpeg;base64,/9j/...');
        
        $expectedMessage = array(
            'role' => 'user',
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => 'What is in this image?'
                ),
                array(
                    'type' => 'image_url',
                    'image_url' => array('url' => 'data:image/jpeg;base64,/9j/...')
                )
            )
        );
        
        $this->assertEquals($expectedMessage, $message);
    }

    public function testImageMessageWithArrayUrl()
    {
        $client = new OpenAI();
        
        $imageUrl = array('url' => 'http://example.com/image.jpg', 'detail' => 'high');
        $message = $client->imageMessage('Describe this image', $imageUrl);
        
        $this->assertEquals('user', $message['role']);
        $this->assertEquals($imageUrl, $message['content'][1]['image_url']);
    }

    public function testImageMessageWithCustomRole()
    {
        $client = new OpenAI();
        
        $message = $client->imageMessage('What do you see?', 'http://example.com/image.jpg', 'assistant');
        
        $this->assertEquals('assistant', $message['role']);
    }

    public function testJsonFormat()
    {
        $client = new OpenAI();
        
        $format = $client->jsonFormat();
        
        $this->assertEquals(array('type' => 'json_object'), $format);
    }

    public function testStreamOptions()
    {
        $client = new OpenAI();
        
        $options = $client->streamOptions();
        $this->assertEquals(array('include_usage' => false), $options);
        
        $optionsWithUsage = $client->streamOptions(true);
        $this->assertEquals(array('include_usage' => true), $optionsWithUsage);
    }

    public function testRetrieveModelWithString()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('/models/llama3.2%3A1b'))
                  ->willReturn(array('id' => 'llama3.2:1b', 'object' => 'model'));

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->retrieveModel('llama3.2:1b');

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('llama3.2:1b', $result['id']);
    }

    public function testRetrieveModelWithModelObject()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('/models/llama3.2%3A1b'))
                  ->willReturn(array('id' => 'llama3.2:1b', 'object' => 'model'));

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $model = new Model('llama3.2:1b');
        $result = $client->retrieveModel($model);

        $this->assertArrayHasKey('id', $result);
    }

    public function testListModels()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('/models'))
                  ->willReturn($this->createModelsListResponse());

        $client = new OpenAI();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->listModels();

        $this->assertArrayHasKey('object', $result);
        $this->assertEquals('list', $result['object']);
        $this->assertArrayHasKey('data', $result);
        $this->assertTrue(is_array($result['data']));
    }
}

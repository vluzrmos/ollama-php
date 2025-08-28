<?php

use Vluzrmos\Ollama\Exceptions\RequiredParameterException;
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Model;

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

        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('parameter "model" is required');

        $client->chatCompletions(array(
            'messages' => array(array('role' => 'user', 'content' => 'Hello'))
        ));
    }

    public function testChatCompletionsRequiresMessages()
    {
        $client = new OpenAI();

        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('parameter "messages" is required');

        $client->chatCompletions(array(
            'model' => 'llama3.2:1b'
        ));
    }

    public function testCompletionsRequiresModel()
    {
        $client = new OpenAI();

        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('parameter "model" is required');

        $client->completions(array(
            'prompt' => 'Hello'
        ));
    }

    public function testCompletionsRequiresPrompt()
    {
        $client = new OpenAI();

        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('parameter "prompt" is required');

        $client->completions(array(
            'model' => 'llama3.2:1b'
        ));
    }

    public function testEmbeddingsRequiresModel()
    {
        $client = new OpenAI();

        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('parameter "model" is required');

        $client->embeddings(array(
            'input' => 'Hello world'
        ));
    }

    public function testEmbeddingsRequiresInput()
    {
        $client = new OpenAI();

        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('parameter "input" is required');

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
                $this->callback(function ($params) {
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

        foreach (array('id', 'object', 'created', 'model', 'choices') as $key) {
            $this->assertArrayHasKey($key, $result);
        }

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
                $this->callback(function ($params) {
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
                $this->callback(function ($params) {
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
                $this->callback(function ($params) {
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

<?php

require_once __DIR__ . '/../TestCase.php';

use Ollama\Ollama;
use Ollama\Models\Model;
use Ollama\Tools\ToolManager;
use Ollama\Exceptions\HttpException;

class OllamaTest extends TestCase
{
    public function testOllamaCreation()
    {
        $client = new Ollama();
        
        $this->assertInstanceOf('Ollama\\Ollama', $client);
        $this->assertInstanceOf('Ollama\\Http\\HttpClient', $client->getHttpClient());
        $this->assertInstanceOf('Ollama\\Tools\\ToolManager', $client->getToolManager());
    }

    public function testOllamaCreationWithParameters()
    {
        $baseUrl = 'http://test.com:11434';
        $options = array('timeout' => 60);
        
        $client = new Ollama($baseUrl, $options);
        
        $this->assertInstanceOf('Ollama\\Http\\HttpClient', $client->getHttpClient());
        $this->assertInstanceOf('Ollama\\Tools\\ToolManager', $client->getToolManager());
    }

    public function testDefaultBaseUrl()
    {
        $client = new Ollama();
        
        // Verifica se o client HTTP foi criado - indiretamente testa a URL padrão
        $this->assertInstanceOf('Ollama\\Http\\HttpClient', $client->getHttpClient());
    }

    public function testSetAndGetApiToken()
    {
        $client = new Ollama();
        
        $this->assertNull($client->getApiToken());
        
        $client->setApiToken('test-token');
        $this->assertEquals('test-token', $client->getApiToken());
    }

    public function testGenerateWithStringModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/generate'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 isset($params['prompt']);
                      })
                  )
                  ->willReturn(array('response' => 'Generated text'));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->generate(array(
            'model' => 'llama3.2:1b',
            'prompt' => 'Hello'
        ));

        $this->assertArrayHasKey('response', $result);
    }

    public function testGenerateWithModelObject()
    {
        $mockClient = $this->createHttpClientMock();
        $model = new Model('llama3.2:1b');
        $model->setTemperature(0.7);
        
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/generate'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 isset($params['options']) &&
                                 is_object($params['options']);
                      })
                  )
                  ->willReturn(array('response' => 'Generated text'));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->generate(array(
            'model' => $model,
            'prompt' => 'Hello'
        ));

        $this->assertArrayHasKey('response', $result);
    }

    public function testChatWithStringModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/chat'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 isset($params['messages']);
                      })
                  )
                  ->willReturn(array('message' => array('content' => 'Chat response')));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->chat(array(
            'model' => 'llama3.2:1b',
            'messages' => array(array('role' => 'user', 'content' => 'Hello'))
        ));

        $this->assertArrayHasKey('message', $result);
    }

    public function testListModels()
    {
        $mockResponse = array(
            'models' => array(
                array('name' => 'llama3.2:1b', 'size' => 1000000),
                array('name' => 'llama3.2:3b', 'size' => 3000000)
            )
        );
        
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('/api/tags'))
                  ->willReturn($mockResponse);

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->listModels();

        $this->assertArrayHasKey('models', $result);
        $this->assertTrue(is_array($result['models']));
        $this->assertEquals(2, count($result['models']));
    }

    public function testShowModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/show'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 !isset($params['verbose']);
                      })
                  )
                  ->willReturn($this->createOllamaShowModelResponse());

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->showModel('llama3.2:1b');

        $this->assertArrayHasKeys(array('license', 'modelfile', 'parameters', 'template'), $result);
    }

    public function testShowModelVerbose()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/show'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 $params['verbose'] === true;
                      })
                  )
                  ->willReturn($this->createOllamaShowModelResponse());

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->showModel('llama3.2:1b', true);

        $this->assertArrayHasKeys(array('license', 'modelfile', 'parameters', 'template'), $result);
    }

    public function testCopyModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/copy'),
                      $this->callback(function($params) {
                          return $params['source'] === 'llama3.2:1b' && 
                                 $params['destination'] === 'my-model';
                      })
                  )
                  ->willReturn(array('success' => true));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->copyModel('llama3.2:1b', 'my-model');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testDeleteModel()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('delete')
                  ->with(
                      $this->equalTo('/api/delete'),
                      $this->callback(function($params) {
                          return $params['model'] === 'my-model';
                      })
                  )
                  ->willReturn(array('success' => true));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->deleteModel('my-model');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testEmbeddings()
    {
        $mockResponse = array(
            'embeddings' => array(array_fill(0, 128, 0.1))
        );
        
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/embed'),
                      $this->callback(function($params) {
                          return $params['model'] === 'llama3.2:1b' && 
                                 $params['input'] === 'Hello world';
                      })
                  )
                  ->willReturn($mockResponse);

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->embeddings(array(
            'model' => 'llama3.2:1b',
            'input' => 'Hello world'
        ));

        $this->assertArrayHasKey('embeddings', $result);
        $this->assertTrue(is_array($result['embeddings']));
    }

    public function testListRunningModels()
    {
        $mockResponse = array(
            'models' => array(
                array(
                    'name' => 'llama3.2:1b',
                    'size' => 1000000,
                    'expires_at' => '2024-01-01T00:00:00Z'
                )
            )
        );
        
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('/api/ps'))
                  ->willReturn($mockResponse);

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->listRunningModels();

        $this->assertArrayHasKey('models', $result);
        $this->assertTrue(is_array($result['models']));
    }

    public function testVersion()
    {
        $mockResponse = array('version' => '0.1.0');
        
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('get')
                  ->with($this->equalTo('/api/version'))
                  ->willReturn($mockResponse);

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->version();

        $this->assertArrayHasKey('version', $result);
        $this->assertEquals('0.1.0', $result['version']);
    }

    public function testBlobExists()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('head')
                  ->with($this->equalTo('/api/blobs/sha256:abc123'))
                  ->willReturn(array('http_code' => 200));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->blobExists('sha256:abc123');

        $this->assertTrue($result);
    }

    public function testBlobNotExists()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('head')
                  ->with($this->equalTo('/api/blobs/sha256:nonexistent'))
                  ->willThrowException(new HttpException('Not found', 404));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->blobExists('sha256:nonexistent');

        $this->assertFalse($result);
    }

    public function testBlobExistsWithOtherError()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('head')
                  ->with($this->equalTo('/api/blobs/sha256:error'))
                  ->willThrowException(new HttpException('Server error', 500));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $this->expectException('Ollama\\Exceptions\\HttpException');
        $this->expectExceptionCode(500);

        $client->blobExists('sha256:error');
    }

    public function testPushBlob()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('put')
                  ->with(
                      $this->equalTo('/api/blobs/sha256:abc123'),
                      $this->equalTo('binary data'),
                      $this->equalTo(array('Content-Type: application/octet-stream'))
                  )
                  ->willReturn(array('success' => true));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->pushBlob('sha256:abc123', 'binary data');

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testToolManagerMethods()
    {
        $client = new Ollama();
        $toolManager = $client->getToolManager();
        
        $this->assertInstanceOf('Ollama\\Tools\\ToolManager', $toolManager);
        
        // Testa métodos que delegam para o ToolManager
        $this->assertTrue(method_exists($client, 'registerTool'));
        $this->assertTrue(method_exists($client, 'executeTool'));
        $this->assertTrue(method_exists($client, 'listAvailableTools'));
        $this->assertTrue(method_exists($client, 'getToolsForAPI'));
    }

    public function testChatWithTools()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/chat'),
                      $this->callback(function($params) {
                          // Como não há tools registradas, o campo tools não deve existir
                          return isset($params['model']) &&
                                 $params['model'] === 'llama3.2:1b' &&
                                 !isset($params['tools']);
                      })
                  )
                  ->willReturn(array('message' => array('content' => 'Chat with tools response')));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->chatWithTools(array(
            'model' => 'llama3.2:1b',
            'messages' => array(array('role' => 'user', 'content' => 'Hello'))
        ));

        $this->assertArrayHasKey('message', $result);
    }

    public function testChatWithToolsDisabled()
    {
        $mockClient = $this->createHttpClientMock();
        $mockClient->expects($this->once())
                  ->method('post')
                  ->with(
                      $this->equalTo('/api/chat'),
                      $this->callback(function($params) {
                          return !isset($params['tools']);
                      })
                  )
                  ->willReturn(array('message' => array('content' => 'Chat without tools response')));

        $client = new Ollama();
        
        // Usar reflection para substituir o HttpClient
        $reflection = new ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockClient);

        $result = $client->chatWithTools(array(
            'model' => 'llama3.2:1b',
            'messages' => array(array('role' => 'user', 'content' => 'Hello'))
        ), null, false);

        $this->assertArrayHasKey('message', $result);
    }
}

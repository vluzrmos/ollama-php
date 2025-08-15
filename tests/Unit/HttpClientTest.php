<?php

require_once __DIR__ . '/../TestCase.php';

use Ollama\Http\HttpClient;
use Ollama\Exceptions\HttpException;
use Ollama\Exceptions\OllamaException;

class HttpClientTest extends TestCase
{
    public function testHttpClientCreation()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $this->assertInstanceOf('Ollama\\Http\\HttpClient', $client);
        $this->assertNull($client->getApiToken());
    }

    public function testHttpClientCreationWithOptions()
    {
        $options = array(
            'timeout' => 60,
            'connect_timeout' => 10,
            'user_agent' => 'Test/1.0',
            'verify_ssl' => false,
            'api_token' => 'test-token'
        );
        
        $client = new HttpClient('http://localhost:11434', $options);
        
        $this->assertEquals('test-token', $client->getApiToken());
    }

    public function testSetAndGetApiToken()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $this->assertNull($client->getApiToken());
        
        $client->setApiToken('new-token');
        $this->assertEquals('new-token', $client->getApiToken());
    }

    public function testBaseUrlTrimming()
    {
        // Este teste verifica indiretamente se a URL é corretamente tratada
        // Vamos usar reflection para acessar a propriedade privada baseUrl
        $client = new HttpClient('http://localhost:11434/');
        
        $reflection = new ReflectionClass($client);
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $baseUrl = $baseUrlProperty->getValue($client);
        
        $this->assertEquals('http://localhost:11434', $baseUrl);
    }

    public function testDefaultOptions()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $reflection = new ReflectionClass($client);
        $optionsProperty = $reflection->getProperty('defaultOptions');
        $optionsProperty->setAccessible(true);
        $options = $optionsProperty->getValue($client);
        
        $expectedDefaults = array(
            'timeout' => 300,
            'connect_timeout' => 30,
            'user_agent' => 'Ollama/1.0',
            'verify_ssl' => true
        );
        
        foreach ($expectedDefaults as $key => $value) {
            $this->assertArrayHasKey($key, $options);
            $this->assertEquals($value, $options[$key]);
        }
    }

    public function testCustomOptionsOverrideDefaults()
    {
        $customOptions = array(
            'timeout' => 60,
            'user_agent' => 'Custom/1.0'
        );
        
        $client = new HttpClient('http://localhost:11434', $customOptions);
        
        $reflection = new ReflectionClass($client);
        $optionsProperty = $reflection->getProperty('defaultOptions');
        $optionsProperty->setAccessible(true);
        $options = $optionsProperty->getValue($client);
        
        $this->assertEquals(60, $options['timeout']);
        $this->assertEquals('Custom/1.0', $options['user_agent']);
        
        // Verifica se outros padrões são mantidos
        $this->assertEquals(30, $options['connect_timeout']);
        $this->assertTrue($options['verify_ssl']);
    }

    /**
     * Testes que requerem mock do curl ou testes de integração real
     * Por simplicidade, vamos testar apenas a interface pública
     * Para testes de integração com curl real, use os testes de integração
     */
    public function testMethodsExist()
    {
        $client = new HttpClient('http://localhost:11434');
        
        // Verifica se os métodos públicos existem
        $this->assertTrue(method_exists($client, 'get'));
        $this->assertTrue(method_exists($client, 'post'));
        $this->assertTrue(method_exists($client, 'put'));
        $this->assertTrue(method_exists($client, 'delete'));
        $this->assertTrue(method_exists($client, 'head'));
        $this->assertTrue(method_exists($client, 'postStream'));
    }

    public function testGetMethodSignature()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $method = new ReflectionMethod($client, 'get');
        $params = $method->getParameters();
        
        $this->assertEquals(2, count($params));
        $this->assertEquals('endpoint', $params[0]->getName());
        $this->assertEquals('headers', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
    }

    public function testPostMethodSignature()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $method = new ReflectionMethod($client, 'post');
        $params = $method->getParameters();
        
        $this->assertEquals(3, count($params));
        $this->assertEquals('endpoint', $params[0]->getName());
        $this->assertEquals('data', $params[1]->getName());
        $this->assertEquals('headers', $params[2]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertTrue($params[2]->isDefaultValueAvailable());
    }

    public function testPostStreamMethodSignature()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $method = new ReflectionMethod($client, 'postStream');
        $params = $method->getParameters();
        
        $this->assertEquals(3, count($params));
        $this->assertEquals('endpoint', $params[0]->getName());
        $this->assertEquals('data', $params[1]->getName());
        $this->assertEquals('callback', $params[2]->getName());
    }

    public function testPutMethodSignature()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $method = new ReflectionMethod($client, 'put');
        $params = $method->getParameters();
        
        $this->assertEquals(3, count($params));
        $this->assertEquals('endpoint', $params[0]->getName());
        $this->assertEquals('data', $params[1]->getName());
        $this->assertEquals('headers', $params[2]->getName());
        $this->assertTrue($params[2]->isDefaultValueAvailable());
    }

    public function testDeleteMethodSignature()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $method = new ReflectionMethod($client, 'delete');
        $params = $method->getParameters();
        
        $this->assertEquals(3, count($params));
        $this->assertEquals('endpoint', $params[0]->getName());
        $this->assertEquals('data', $params[1]->getName());
        $this->assertEquals('headers', $params[2]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertTrue($params[2]->isDefaultValueAvailable());
    }

    public function testHeadMethodSignature()
    {
        $client = new HttpClient('http://localhost:11434');
        
        $method = new ReflectionMethod($client, 'head');
        $params = $method->getParameters();
        
        $this->assertEquals(2, count($params));
        $this->assertEquals('endpoint', $params[0]->getName());
        $this->assertEquals('headers', $params[1]->getName());
        $this->assertTrue($params[1]->isDefaultValueAvailable());
    }
}

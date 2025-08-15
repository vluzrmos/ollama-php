<?php

namespace Ollama\Tests;

use PHPUnit_Framework_TestCase;
use Ollama\Ollama;
use Ollama\Http\HttpClient;

class ApiTokenTest extends PHPUnit_Framework_TestCase
{
    public function testTokenInConstructor()
    {
        $client = new Ollama('http://localhost:11434', array(
            'api_token' => 'test-token'
        ));

        $this->assertEquals('test-token', $client->getApiToken());
    }

    public function testSetApiToken()
    {
        $client = new Ollama('http://localhost:11434');
        
        $this->assertNull($client->getApiToken());
        
        $client->setApiToken('new-token');
        
        $this->assertEquals('new-token', $client->getApiToken());
    }

    public function testHttpClientTokenConfiguration()
    {
        $httpClient = new HttpClient('http://localhost:11434', array(
            'api_token' => 'http-test-token'
        ));

        $this->assertEquals('http-test-token', $httpClient->getApiToken());
    }

    public function testHttpClientSetToken()
    {
        $httpClient = new HttpClient('http://localhost:11434');
        
        $this->assertNull($httpClient->getApiToken());
        
        $httpClient->setApiToken('http-new-token');
        
        $this->assertEquals('http-new-token', $httpClient->getApiToken());
    }

    public function testOpenAiCompatibility()
    {
        $client = new Ollama('https://api.openai.com/v1', array(
            'api_token' => 'sk-fake-openai-token'
        ));

        $this->assertEquals('sk-fake-openai-token', $client->getApiToken());
    }

    public function testDynamicTokenChange()
    {
        $client = new Ollama('http://localhost:11434', array(
            'api_token' => 'initial-token'
        ));

        $this->assertEquals('initial-token', $client->getApiToken());
        
        $client->setApiToken('updated-token');
        
        $this->assertEquals('updated-token', $client->getApiToken());
        
        $client->setApiToken(null);
        
        $this->assertNull($client->getApiToken());
    }
}

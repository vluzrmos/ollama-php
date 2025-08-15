<?php

require_once __DIR__ . '/../TestCase.php';

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Model;

class OpenAIIntegrationTest extends TestCase
{
    /**
     * @var OpenAI
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();
        $this->skipIfIntegrationDisabled();
        
        $this->client = new OpenAI($this->getOpenAIBaseUrl(), 'ollama');
    }

    public function testListModels()
    {
        $result = $this->client->listModels();
        
        $this->assertArrayHasKey('object', $result);
        $this->assertEquals('list', $result['object']);
        $this->assertArrayHasKey('data', $result);
        $this->assertTrue(is_array($result['data']));
        
        if (!empty($result['data'])) {
            $model = $result['data'][0];
            $this->assertArrayHasKeys(array('id', 'object'), $model);
            $this->assertEquals('model', $model['object']);
        }
    }

    public function testChatCompletion()
    {
        $messages = array(
            $this->client->systemMessage('You are a helpful assistant.'),
            $this->client->userMessage('Say "Hello, World!" and nothing else.')
        );
        
        $result = $this->client->chat($this->getTestModel(), $messages, array(
            'max_tokens' => 10,
            'temperature' => 0.1
        ));
        
        $this->assertArrayHasKeys(array('id', 'object', 'created', 'model', 'choices'), $result);
        $this->assertEquals('chat.completion', $result['object']);
        $this->assertTrue(is_array($result['choices']));
        $this->assertGreaterThan(0, count($result['choices']));
        
        $choice = $result['choices'][0];
        $this->assertArrayHasKeys(array('index', 'message', 'finish_reason'), $choice);
        $this->assertArrayHasKeys(array('role', 'content'), $choice['message']);
        $this->assertEquals('assistant', $choice['message']['role']);
        $this->assertNotEmpty($choice['message']['content']);
    }

    public function testChatCompletionWithModel()
    {
        $model = new Model($this->getTestModel());
        $model->setTemperature(0.1)
              ->setTopP(0.9)
              ->setNumPredict(10);
        
        $messages = array(
            $this->client->userMessage('Respond with exactly one word: "test"')
        );
        
        $result = $this->client->chat($model, $messages);
        
        $this->assertArrayHasKeys(array('id', 'object', 'created', 'model', 'choices'), $result);
        $this->assertEquals('chat.completion', $result['object']);
        
        $choice = $result['choices'][0];
        $this->assertEquals('assistant', $choice['message']['role']);
        $this->assertNotEmpty($choice['message']['content']);
    }

    public function testCompletion()
    {
        $result = $this->client->complete($this->getTestModel(), 'Once upon a time', array(
            'max_tokens' => 20,
            'temperature' => 0.1,
            'stop' => array('.', '!', '?')
        ));
        
        $this->assertArrayHasKeys(array('id', 'object', 'created', 'model', 'choices'), $result);
        $this->assertEquals('text_completion', $result['object']);
        $this->assertTrue(is_array($result['choices']));
        $this->assertGreaterThan(0, count($result['choices']));
        
        $choice = $result['choices'][0];
        $this->assertArrayHasKeys(array('text', 'index', 'finish_reason'), $choice);
        $this->assertEquals(0, $choice['index']);
        $this->assertNotEmpty($choice['text']);
    }

    public function testEmbeddings()
    {
        $result = $this->client->embed($this->getTestModel(), 'Hello, world!');
        
        $this->assertArrayHasKeys(array('object', 'data', 'model', 'usage'), $result);
        $this->assertEquals('list', $result['object']);
        $this->assertTrue(is_array($result['data']));
        $this->assertGreaterThan(0, count($result['data']));
        
        $embedding = $result['data'][0];
        $this->assertArrayHasKeys(array('object', 'embedding', 'index'), $embedding);
        $this->assertEquals('embedding', $embedding['object']);
        $this->assertEquals(0, $embedding['index']);
        $this->assertTrue(is_array($embedding['embedding']));
        $this->assertGreaterThan(0, count($embedding['embedding']));
        
        // Verifica se os embeddings são números
        foreach ($embedding['embedding'] as $value) {
            $this->assertTrue(is_numeric($value));
        }
    }

    public function testEmbeddingsWithMultipleInputs()
    {
        $inputs = array(
            'Hello, world!',
            'How are you today?',
            'This is a test.'
        );
        
        $result = $this->client->embed($this->getTestModel(), $inputs);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(3, count($result['data']));
        
        foreach ($result['data'] as $i => $embedding) {
            $this->assertEquals($i, $embedding['index']);
            $this->assertTrue(is_array($embedding['embedding']));
            $this->assertGreaterThan(0, count($embedding['embedding']));
        }
    }

    public function testRetrieveModel()
    {
        $modelName = $this->getTestModel();
        $result = $this->client->retrieveModel($modelName);
        
        $this->assertArrayHasKeys(array('id', 'object'), $result);
        $this->assertEquals($modelName, $result['id']);
        $this->assertEquals('model', $result['object']);
    }

    public function testChatCompletionWithImageMessage()
    {
        // Criar uma imagem base64 mínima (1x1 pixel PNG transparente)
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
        
        $message = $this->client->imageMessage('Describe this image', $base64Image);
        
        try {
            $result = $this->client->chat($this->getTestModel(), array($message), array(
                'max_tokens' => 50,
                'temperature' => 0.1
            ));
            
            $this->assertArrayHasKey('choices', $result);
            $this->assertGreaterThan(0, count($result['choices']));
            $this->assertEquals('assistant', $result['choices'][0]['message']['role']);
            
        } catch (Exception $e) {
            // Alguns modelos podem não suportar visão
            $this->markTestSkipped('Model may not support vision capabilities: ' . $e->getMessage());
        }
    }

    public function testJsonFormat()
    {
        $messages = array(
            $this->client->systemMessage('You must respond in valid JSON format.'),
            $this->client->userMessage('Return a JSON object with name "test" and value 123.')
        );
        
        try {
            $result = $this->client->chat($this->getTestModel(), $messages, array(
                'response_format' => $this->client->jsonFormat(),
                'max_tokens' => 50,
                'temperature' => 0.1
            ));
            
            $content = $result['choices'][0]['message']['content'];
            $this->assertIsValidJson($content, 'Response should be valid JSON');
            
            $decoded = json_decode($content, true);
            $this->assertTrue(is_array($decoded), 'JSON should decode to array');
            
        } catch (Exception $e) {
            // Nem todos os modelos suportam JSON format
            $this->markTestSkipped('Model may not support JSON format: ' . $e->getMessage());
        }
    }

    public function testStreamingChat()
    {
        $messages = array(
            $this->client->userMessage('Count from 1 to 5, each number on a new line.')
        );
        
        $chunks = array();
        $callback = function($chunk) use (&$chunks) {
            $chunks[] = $chunk;
            // Verifica estrutura básica de cada chunk
            if (isset($chunk['choices']) && !empty($chunk['choices'])) {
                $this->assertArrayHasKey('delta', $chunk['choices'][0]);
            }
        };
        
        $this->client->chatStream($this->getTestModel(), $messages, $callback, array(
            'max_tokens' => 20,
            'temperature' => 0.1
        ));
        
        $this->assertGreaterThan(0, count($chunks), 'Should receive streaming chunks');
        
        // Verifica se ao menos um chunk contém conteúdo
        $hasContent = false;
        foreach ($chunks as $chunk) {
            if (isset($chunk['choices'][0]['delta']['content']) && 
                !empty($chunk['choices'][0]['delta']['content'])) {
                $hasContent = true;
                break;
            }
        }
        $this->assertTrue($hasContent, 'At least one chunk should contain content');
    }

    public function testStreamingCompletion()
    {
        $chunks = array();
        $callback = function($chunk) use (&$chunks) {
            $chunks[] = $chunk;
        };
        
        $this->client->completeStream($this->getTestModel(), 'The weather today is', $callback, array(
            'max_tokens' => 15,
            'temperature' => 0.1
        ));
        
        $this->assertGreaterThan(0, count($chunks), 'Should receive streaming chunks');
    }

    public function testChatCompletionWithSystemAndUserMessages()
    {
        $messages = array(
            $this->client->systemMessage('You are a helpful math tutor. Always show your work.'),
            $this->client->userMessage('What is 2 + 2?')
        );
        
        $result = $this->client->chat($this->getTestModel(), $messages, array(
            'max_tokens' => 100,
            'temperature' => 0.1
        ));
        
        $this->assertArrayHasKey('choices', $result);
        $content = $result['choices'][0]['message']['content'];
        $this->assertNotEmpty($content);
        
        // O conteúdo deve mencionar o número 4 em algum lugar
        $this->assertTrue(
            strpos(strtolower($content), '4') !== false,
            'Response should contain the answer 4'
        );
    }

    public function testErrorHandling()
    {
        try {
            // Tentar usar um modelo que não existe
            $this->client->chat('nonexistent-model-xyz', array(
                $this->client->userMessage('Hello')
            ));
            
            $this->fail('Should have thrown an exception for nonexistent model');
            
        } catch (Exception $e) {
            // Deve lançar uma exceção
            $this->assertInstanceOf('Exception', $e);
            $this->assertGreaterThan(0, strlen($e->getMessage()));
        }
    }
}

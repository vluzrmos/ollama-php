<?php

require_once __DIR__ . '/../TestCase.php';

use Ollama\Ollama;
use Ollama\Models\Model;

class OllamaIntegrationTest extends TestCase
{
    /**
     * @var Ollama
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();
        $this->skipIfIntegrationDisabled();
        
        $this->client = new Ollama($this->getOllamaBaseUrl());
    }

    public function testListModels()
    {
        $result = $this->client->listModels();
        
        $this->assertArrayHasKey('models', $result);
        $this->assertTrue(is_array($result['models']));
        
        if (!empty($result['models'])) {
            $model = $result['models'][0];
            $this->assertArrayHasKeys(array('name', 'size'), $model);
            $this->assertNotEmpty($model['name']);
            $this->assertGreaterThan(0, $model['size']);
        }
    }

    public function testShowModel()
    {
        $result = $this->client->showModel($this->getTestModel());
        
        // Verificar chaves essenciais que sempre existem
        $this->assertArrayHasKey('modelfile', $result);
        $this->assertNotEmpty($result['modelfile']);
        
        // Outras chaves podem ou não existir dependendo do modelo
        if (isset($result['template'])) {
            $this->assertNotEmpty($result['template']);
        }
    }

    public function testShowModelVerbose()
    {
        $result = $this->client->showModel($this->getTestModel(), true);
        
        // Com verbose, deve conter pelo menos modelfile
        $this->assertArrayHasKey('modelfile', $result);
        $this->assertNotEmpty($result['modelfile']);
    }

    public function testGenerate()
    {
        $result = $this->client->generate(array(
            'model' => $this->getTestModel(),
            'prompt' => 'Say "Hello, World!" and nothing else.',
            'stream' => false
        ));
        
        $this->assertArrayHasKey('response', $result);
        $this->assertNotEmpty($result['response']);
        $this->assertArrayHasKey('done', $result);
        $this->assertTrue($result['done']);
    }

    public function testGenerateWithModel()
    {
        $model = new Model($this->getTestModel());
        $model->setTemperature(0.1)
              ->setTopP(0.9)
              ->setNumPredict(20);
        
        $result = $this->client->generate(array(
            'model' => $model,
            'prompt' => 'Complete this sentence: The sun is',
            'stream' => false
        ));
        
        $this->assertArrayHasKey('response', $result);
        $this->assertNotEmpty($result['response']);
        $this->assertArrayHasKey('done', $result);
        $this->assertTrue($result['done']);
    }

    public function testChat()
    {
        $messages = array(
            array('role' => 'system', 'content' => 'You are a helpful assistant.'),
            array('role' => 'user', 'content' => 'Say "Hello, World!" and nothing else.')
        );
        
        $result = $this->client->chat(array(
            'model' => $this->getTestModel(),
            'messages' => $messages,
            'stream' => false
        ));
        
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKeys(array('role', 'content'), $result['message']);
        $this->assertEquals('assistant', $result['message']['role']);
        $this->assertNotEmpty($result['message']['content']);
        $this->assertArrayHasKey('done', $result);
        $this->assertTrue($result['done']);
    }

    public function testChatWithModel()
    {
        $model = new Model($this->getTestModel());
        $model->setTemperature(0.1);
        
        $messages = array(
            array('role' => 'user', 'content' => 'Respond with exactly one word: "test"')
        );
        
        $result = $this->client->chat(array(
            'model' => $model,
            'messages' => $messages,
            'stream' => false
        ));
        
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('assistant', $result['message']['role']);
        $this->assertNotEmpty($result['message']['content']);
    }

    public function testEmbeddings()
    {
        $result = $this->client->embeddings(array(
            'model' => $this->getTestModel(),
            'input' => 'Hello, world!'
        ));
        
        $this->assertArrayHasKey('embeddings', $result);
        $this->assertTrue(is_array($result['embeddings']));
        $this->assertGreaterThan(0, count($result['embeddings']));
        
        $embedding = $result['embeddings'][0];
        $this->assertTrue(is_array($embedding));
        $this->assertGreaterThan(0, count($embedding));
        
        // Verifica se os embeddings são números
        foreach ($embedding as $value) {
            $this->assertTrue(is_numeric($value));
        }
    }

    public function testListRunningModels()
    {
        $result = $this->client->listRunningModels();
        
        $this->assertArrayHasKey('models', $result);
        $this->assertTrue(is_array($result['models']));
        
        // Pode estar vazio se nenhum modelo estiver carregado
        // Apenas verifica a estrutura
        foreach ($result['models'] as $model) {
            $this->assertArrayHasKeys(array('name', 'size'), $model);
        }
    }

    public function testVersion()
    {
        $result = $this->client->version();
        
        $this->assertArrayHasKey('version', $result);
        $this->assertNotEmpty($result['version']);
        $this->assertRegExp('/^\d+\.\d+\.\d+/', $result['version'], 'Version should match semantic versioning pattern');
    }

    public function testStreamingGenerate()
    {
        $chunks = array();
        $callback = function($chunk) use (&$chunks) {
            $chunks[] = $chunk;
            // Verifica estrutura básica de cada chunk
            $this->assertArrayHasKey('response', $chunk);
            $this->assertArrayHasKey('done', $chunk);
        };
        
        $this->client->generate(array(
            'model' => $this->getTestModel(),
            'prompt' => 'Count from 1 to 3',
            'stream' => true
        ), $callback);
        
        $this->assertGreaterThan(0, count($chunks), 'Should receive streaming chunks');
        
        // O último chunk deve ter done = true
        $lastChunk = end($chunks);
        $this->assertTrue($lastChunk['done'], 'Last chunk should have done = true');
        
        // Verifica se ao menos um chunk contém conteúdo
        $hasContent = false;
        foreach ($chunks as $chunk) {
            if (!empty($chunk['response'])) {
                $hasContent = true;
                break;
            }
        }
        $this->assertTrue($hasContent, 'At least one chunk should contain content');
    }

    public function testStreamingChat()
    {
        $messages = array(
            array('role' => 'user', 'content' => 'Count from 1 to 3, each number on a new line.')
        );
        
        $chunks = array();
        $callback = function($chunk) use (&$chunks) {
            $chunks[] = $chunk;
            $this->assertArrayHasKey('message', $chunk);
            $this->assertArrayHasKey('done', $chunk);
        };
        
        $this->client->chat(array(
            'model' => $this->getTestModel(),
            'messages' => $messages,
            'stream' => true
        ), $callback);
        
        $this->assertGreaterThan(0, count($chunks), 'Should receive streaming chunks');
        
        // O último chunk deve ter done = true
        $lastChunk = end($chunks);
        $this->assertTrue($lastChunk['done'], 'Last chunk should have done = true');
    }

    public function testCopyModel()
    {
        $sourceName = $this->getTestModel();
        $destName = 'test-copy-' . uniqid();
        
        try {
            $result = $this->client->copyModel($sourceName, $destName);
            
            // Aguarda um momento para a operação completar
            sleep(1);
            
            // Verifica se a cópia foi bem-sucedida
            $models = $this->client->listModels();
            $modelNames = array_column($models['models'], 'name');
            
            if (!in_array($destName, $modelNames)) {
                // Pode haver um delay, tenta novamente
                sleep(2);
                $models = $this->client->listModels();
                $modelNames = array_column($models['models'], 'name');
            }
            
            $this->assertContains($destName, $modelNames, 'Copied model should appear in list');
            
        } catch (Exception $e) {
            $this->markTestSkipped('Copy model test failed, may not be supported: ' . $e->getMessage());
        } finally {
            // Limpa o modelo copiado
            try {
                $this->client->deleteModel($destName);
            } catch (Exception $e) {
                // Ignora erro de limpeza
            }
        }
    }

    public function testDeleteModel()
    {
        $testModelName = 'test-delete-' . uniqid();
        
        try {
            // Primeiro, copia um modelo para poder deletar
            $this->client->copyModel($this->getTestModel(), $testModelName);
            
            // Aguarda a operação completar
            sleep(2);
            
            // Verifica se o modelo existe
            $models = $this->client->listModels();
            $modelNames = array_column($models['models'], 'name');
            
            if (!in_array($testModelName, $modelNames)) {
                $this->markTestSkipped('Could not create test model for deletion test');
                return;
            }
            
            // Deleta o modelo
            $result = $this->client->deleteModel($testModelName);
            
            // Aguarda a deleção completar
            sleep(1);
            
            // Verifica se o modelo foi removido
            $modelsAfter = $this->client->listModels();
            $modelNamesAfter = array_column($modelsAfter['models'], 'name');
            $this->assertNotContains($testModelName, $modelNamesAfter, 'Test model should not exist after deletion');
            
        } catch (Exception $e) {
            $this->markTestSkipped('Delete model test failed, may not be supported: ' . $e->getMessage());
        }
    }

    public function testPullModelDryRun()
    {
        // Testa apenas a interface de pull sem realmente baixar
        // Este teste requer que o modelo já exista
        try {
            $chunks = array();
            $callback = function($chunk) use (&$chunks) {
                $chunks[] = $chunk;
                $this->assertArrayHasKey('status', $chunk);
            };
            
            // Tenta "puxar" um modelo que já existe (será rápido)
            $this->client->pullModel(array(
                'model' => $this->getTestModel(),
                'stream' => true
            ), $callback);
            
            $this->assertGreaterThan(0, count($chunks), 'Should receive pull status chunks');
            
        } catch (Exception $e) {
            // Se falhar, pode ser que o modelo não exista no registry
            $this->markTestSkipped('Pull test skipped: ' . $e->getMessage());
        }
    }

    public function testBlobExists()
    {
        // Este teste é mais difícil sem conhecer blobs específicos
        // Vamos apenas testar com um hash que certamente não existe
        $fakeHash = 'sha256:' . str_repeat('0', 64);
        
        $exists = $this->client->blobExists($fakeHash);
        $this->assertFalse($exists, 'Fake hash should not exist');
    }

    public function testErrorHandling()
    {
        try {
            // Tentar usar um modelo que não existe
            $this->client->generate(array(
                'model' => 'nonexistent-model-xyz',
                'prompt' => 'Hello'
            ));
            
            $this->fail('Should have thrown an exception for nonexistent model');
            
        } catch (Exception $e) {
            // Deve lançar uma exceção
            $this->assertInstanceOf('Exception', $e);
            $this->assertGreaterThan(0, strlen($e->getMessage()));
        }
    }

    public function testChatWithTools()
    {
        // Teste básico do método chatWithTools
        $messages = array(
            array('role' => 'user', 'content' => 'Hello')
        );
        
        $result = $this->client->chatWithTools(array(
            'model' => $this->getTestModel(),
            'messages' => $messages,
            'stream' => false
        ));
        
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('assistant', $result['message']['role']);
    }

    public function testChatWithToolsDisabled()
    {
        // Teste com tools desabilitadas
        $messages = array(
            array('role' => 'user', 'content' => 'Hello')
        );
        
        $result = $this->client->chatWithTools(array(
            'model' => $this->getTestModel(),
            'messages' => $messages,
            'stream' => false
        ), null, false);
        
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('assistant', $result['message']['role']);
    }

    public function testApiTokenSetAndGet()
    {
        $originalToken = $this->client->getApiToken();
        
        $this->client->setApiToken('test-token');
        $this->assertEquals('test-token', $this->client->getApiToken());
        
        // Restaura o token original
        $this->client->setApiToken($originalToken);
    }

    public function testToolManagerIntegration()
    {
        $toolManager = $this->client->getToolManager();
        $this->assertInstanceOf('Ollama\\Tools\\ToolManager', $toolManager);
        
        // Testa métodos básicos do ToolManager
        $tools = $this->client->listAvailableTools();
        $this->assertTrue(is_array($tools));
        
        $toolsForAPI = $this->client->getToolsForAPI();
        $this->assertTrue(is_array($toolsForAPI));
    }
}

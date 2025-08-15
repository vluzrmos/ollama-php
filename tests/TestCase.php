<?php

use PHPUnit_Framework_TestCase;

/**
 * Classe base para todos os testes
 * Fornece utilitários comuns e configurações para PHP 5.6
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Obtém a URL base do Ollama das variáveis de ambiente ou usa o padrão
     *
     * @return string
     */
    protected function getOllamaBaseUrl()
    {
        return getenv('OLLAMA_API_URL') ?: 'http://localhost:11434';
    }

    /**
     * Obtém a URL base do OpenAI das variáveis de ambiente ou usa o padrão
     *
     * @return string
     */
    protected function getOpenAIBaseUrl()
    {
        return getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1';
    }

    /**
     * Obtém um modelo de teste padrão
     *
     * @return string
     */
    protected function getTestModel()
    {
        return getenv('TEST_MODEL') ?: 'llama3.2:1b';
    }

    /**
     * Verifica se os testes de integração devem ser executados
     *
     * @return bool
     */
    protected function shouldRunIntegrationTests()
    {
        return getenv('RUN_INTEGRATION_TESTS') === '1' || getenv('RUN_INTEGRATION_TESTS') === 'true';
    }

    /**
     * Pula o teste se os testes de integração estão desabilitados
     *
     * @return void
     */
    protected function skipIfIntegrationDisabled()
    {
        if (!$this->shouldRunIntegrationTests()) {
            $this->markTestSkipped('Testes de integração desabilitados. Use RUN_INTEGRATION_TESTS=1 para habilitar.');
        }
    }

    /**
     * Cria um mock para HttpClient
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createHttpClientMock()
    {
        return $this->getMockBuilder('Vluzrmos\\Ollama\\Http\\HttpClient')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Cria dados de resposta mock para chat completions
     *
     * @param string $content
     * @param string $model
     * @return array
     */
    protected function createChatCompletionResponse($content = 'Resposta de teste', $model = 'llama3.2:1b')
    {
        return array(
            'id' => 'chatcmpl-' . uniqid(),
            'object' => 'chat.completion',
            'created' => time(),
            'model' => $model,
            'choices' => array(
                array(
                    'index' => 0,
                    'message' => array(
                        'role' => 'assistant',
                        'content' => $content
                    ),
                    'finish_reason' => 'stop'
                )
            ),
            'usage' => array(
                'prompt_tokens' => 10,
                'completion_tokens' => 5,
                'total_tokens' => 15
            )
        );
    }

    /**
     * Cria dados de resposta mock para completions
     *
     * @param string $text
     * @param string $model
     * @return array
     */
    protected function createCompletionResponse($text = 'Resposta de teste', $model = 'llama3.2:1b')
    {
        return array(
            'id' => 'cmpl-' . uniqid(),
            'object' => 'text_completion',
            'created' => time(),
            'model' => $model,
            'choices' => array(
                array(
                    'text' => $text,
                    'index' => 0,
                    'logprobs' => null,
                    'finish_reason' => 'stop'
                )
            ),
            'usage' => array(
                'prompt_tokens' => 10,
                'completion_tokens' => 5,
                'total_tokens' => 15
            )
        );
    }

    /**
     * Cria dados de resposta mock para embeddings
     *
     * @param array $embedding
     * @param string $model
     * @return array
     */
    protected function createEmbeddingResponse($embedding = null, $model = 'llama3.2:1b')
    {
        if ($embedding === null) {
            // Cria um embedding fake de 128 dimensões
            $embedding = array_fill(0, 128, 0.1);
        }

        return array(
            'object' => 'list',
            'data' => array(
                array(
                    'object' => 'embedding',
                    'embedding' => $embedding,
                    'index' => 0
                )
            ),
            'model' => $model,
            'usage' => array(
                'prompt_tokens' => 5,
                'total_tokens' => 5
            )
        );
    }

    /**
     * Cria dados de resposta mock para lista de modelos
     *
     * @return array
     */
    protected function createModelsListResponse()
    {
        return array(
            'object' => 'list',
            'data' => array(
                array(
                    'id' => 'llama3.2:1b',
                    'object' => 'model',
                    'created' => time(),
                    'owned_by' => 'ollama'
                ),
                array(
                    'id' => 'llama3.2:3b',
                    'object' => 'model',
                    'created' => time(),
                    'owned_by' => 'ollama'
                )
            )
        );
    }

    /**
     * Cria dados de resposta mock para show model do Ollama
     *
     * @param string $model
     * @return array
     */
    protected function createOllamaShowModelResponse($model = 'llama3.2:1b')
    {
        return array(
            'license' => 'MIT',
            'modelfile' => "FROM {$model}",
            'parameters' => 'temperature 0.7',
            'template' => '{{ .Prompt }}',
            'details' => array(
                'format' => 'gguf',
                'family' => 'llama',
                'families' => array('llama'),
                'parameter_size' => '1B',
                'quantization_level' => 'Q4_0'
            )
        );
    }

    /**
     * Asserta que um array contém todas as chaves especificadas
     *
     * @param array $expectedKeys
     * @param array $array
     * @param string $message
     */
    protected function assertArrayHasKeys(array $expectedKeys, array $array, $message = '')
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, $message . " (chave: $key)");
        }
    }

    /**
     * Asserta que uma string é JSON válido
     *
     * @param string $string
     * @param string $message
     */
    protected function assertIsValidJson($string, $message = '')
    {
        json_decode($string);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error(), $message ?: 'String não é JSON válido');
    }

    /**
     * Cria uma mensagem de chat para testes
     *
     * @param string $role
     * @param string $content
     * @return array
     */
    protected function createMessage($role = 'user', $content = 'Olá, como você está?')
    {
        return array(
            'role' => $role,
            'content' => $content
        );
    }
}

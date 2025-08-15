<?php

/**
 * Dados de exemplo e fixtures para testes
 */
class TestFixtures
{
    /**
     * Mensagens de exemplo para chat
     *
     * @return array
     */
    public static function sampleChatMessages()
    {
        return array(
            array(
                'role' => 'system',
                'content' => 'You are a helpful assistant.'
            ),
            array(
                'role' => 'user',
                'content' => 'Hello! How are you?'
            ),
            array(
                'role' => 'assistant',
                'content' => 'Hi there! I\'m doing well, thank you for asking. How can I help you today?'
            ),
            array(
                'role' => 'user',
                'content' => 'Can you tell me a short joke?'
            )
        );
    }

    /**
     * Prompts de exemplo para completions
     *
     * @return array
     */
    public static function samplePrompts()
    {
        return array(
            'simple' => 'Once upon a time',
            'question' => 'What is the capital of France?',
            'math' => 'Calculate 2 + 2 =',
            'creative' => 'Write a short poem about the ocean:',
            'code' => 'Write a PHP function that adds two numbers:',
            'json' => 'Return a JSON object with name and age fields:'
        );
    }

    /**
     * Textos de exemplo para embeddings
     *
     * @return array
     */
    public static function sampleTextsForEmbeddings()
    {
        return array(
            'Hello, world!',
            'The weather is nice today.',
            'PHP is a programming language.',
            'Artificial intelligence is fascinating.',
            'The quick brown fox jumps over the lazy dog.'
        );
    }

    /**
     * Configurações de modelo para testes
     *
     * @return array
     */
    public static function modelConfigurations()
    {
        return array(
            'creative' => array(
                'temperature' => 0.9,
                'top_p' => 0.95,
                'top_k' => 40,
                'repeat_penalty' => 1.1
            ),
            'precise' => array(
                'temperature' => 0.1,
                'top_p' => 0.5,
                'top_k' => 10,
                'repeat_penalty' => 1.0
            ),
            'balanced' => array(
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 20,
                'repeat_penalty' => 1.05
            ),
            'fast' => array(
                'temperature' => 0.5,
                'num_predict' => 50,
                'num_ctx' => 1024
            ),
            'detailed' => array(
                'temperature' => 0.7,
                'num_predict' => 200,
                'num_ctx' => 4096
            )
        );
    }

    /**
     * Opções de streaming para testes
     *
     * @return array
     */
    public static function streamingOptions()
    {
        return array(
            'basic' => array(
                'stream' => true
            ),
            'with_usage' => array(
                'stream' => true,
                'stream_options' => array('include_usage' => true)
            )
        );
    }

    /**
     * Casos de teste para validação de erros
     *
     * @return array
     */
    public static function errorTestCases()
    {
        return array(
            'missing_model' => array(
                'params' => array(
                    'messages' => array(array('role' => 'user', 'content' => 'Hello'))
                ),
                'expected_error' => 'O parâmetro "model" é obrigatório'
            ),
            'missing_messages' => array(
                'params' => array(
                    'model' => 'llama3.2:1b'
                ),
                'expected_error' => 'O parâmetro "messages" é obrigatório'
            ),
            'missing_prompt' => array(
                'params' => array(
                    'model' => 'llama3.2:1b'
                ),
                'expected_error' => 'O parâmetro "prompt" é obrigatório'
            ),
            'missing_input' => array(
                'params' => array(
                    'model' => 'llama3.2:1b'
                ),
                'expected_error' => 'O parâmetro "input" é obrigatório'
            )
        );
    }

    /**
     * Imagens base64 para testes (1x1 pixel PNG)
     *
     * @return array
     */
    public static function base64Images()
    {
        return array(
            'transparent' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'red' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'blue' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAGA60e6kgAAAABJRU5ErkJggg=='
        );
    }

    /**
     * URLs de imagem para testes
     *
     * @return array
     */
    public static function imageUrls()
    {
        return array(
            'basic' => 'https://example.com/image.jpg',
            'with_detail' => array(
                'url' => 'https://example.com/high-res.jpg',
                'detail' => 'high'
            ),
            'low_detail' => array(
                'url' => 'https://example.com/low-res.jpg',
                'detail' => 'low'
            )
        );
    }

    /**
     * Modelos de exemplo para testes
     *
     * @return array
     */
    public static function sampleModelNames()
    {
        return array(
            'llama3.2:1b',
            'llama3.2:3b',
            'llama3.2:7b',
            'llama3.2:latest',
            'mistral:7b',
            'codellama:7b',
            'phi3:mini',
            'qwen2:0.5b',
            'gemma2:2b'
        );
    }

    /**
     * Respostas mock para diferentes tipos de API
     *
     * @return array
     */
    public static function mockResponses()
    {
        return array(
            'chat_completion' => array(
                'id' => 'chatcmpl-123',
                'object' => 'chat.completion',
                'created' => 1234567890,
                'model' => 'llama3.2:1b',
                'choices' => array(
                    array(
                        'index' => 0,
                        'message' => array(
                            'role' => 'assistant',
                            'content' => 'Hello! How can I help you today?'
                        ),
                        'finish_reason' => 'stop'
                    )
                ),
                'usage' => array(
                    'prompt_tokens' => 10,
                    'completion_tokens' => 9,
                    'total_tokens' => 19
                )
            ),
            'completion' => array(
                'id' => 'cmpl-123',
                'object' => 'text_completion',
                'created' => 1234567890,
                'model' => 'llama3.2:1b',
                'choices' => array(
                    array(
                        'text' => ', there lived a young programmer who loved PHP.',
                        'index' => 0,
                        'logprobs' => null,
                        'finish_reason' => 'stop'
                    )
                ),
                'usage' => array(
                    'prompt_tokens' => 4,
                    'completion_tokens' => 10,
                    'total_tokens' => 14
                )
            ),
            'embedding' => array(
                'object' => 'list',
                'data' => array(
                    array(
                        'object' => 'embedding',
                        'embedding' => array_fill(0, 768, 0.1),
                        'index' => 0
                    )
                ),
                'model' => 'llama3.2:1b',
                'usage' => array(
                    'prompt_tokens' => 3,
                    'total_tokens' => 3
                )
            ),
            'models_list' => array(
                'object' => 'list',
                'data' => array(
                    array(
                        'id' => 'llama3.2:1b',
                        'object' => 'model',
                        'created' => 1234567890,
                        'owned_by' => 'ollama'
                    ),
                    array(
                        'id' => 'llama3.2:3b',
                        'object' => 'model',
                        'created' => 1234567890,
                        'owned_by' => 'ollama'
                    )
                )
            ),
            'ollama_generate' => array(
                'model' => 'llama3.2:1b',
                'created_at' => '2024-01-01T00:00:00.000Z',
                'response' => 'Hello! How can I help you today?',
                'done' => true,
                'context' => array(1, 2, 3, 4, 5),
                'total_duration' => 1234567890,
                'load_duration' => 123456789,
                'prompt_eval_count' => 10,
                'prompt_eval_duration' => 123456789,
                'eval_count' => 9,
                'eval_duration' => 123456789
            ),
            'ollama_chat' => array(
                'model' => 'llama3.2:1b',
                'created_at' => '2024-01-01T00:00:00.000Z',
                'message' => array(
                    'role' => 'assistant',
                    'content' => 'Hello! How can I help you today?'
                ),
                'done' => true,
                'total_duration' => 1234567890,
                'load_duration' => 123456789,
                'prompt_eval_count' => 10,
                'prompt_eval_duration' => 123456789,
                'eval_count' => 9,
                'eval_duration' => 123456789
            ),
            'ollama_tags' => array(
                'models' => array(
                    array(
                        'name' => 'llama3.2:1b',
                        'model' => 'llama3.2:1b',
                        'modified_at' => '2024-01-01T00:00:00.000Z',
                        'size' => 1073741824,
                        'digest' => 'sha256:abc123...',
                        'details' => array(
                            'parent_model' => '',
                            'format' => 'gguf',
                            'family' => 'llama',
                            'families' => array('llama'),
                            'parameter_size' => '1B',
                            'quantization_level' => 'Q4_0'
                        )
                    )
                )
            )
        );
    }

    /**
     * Headers HTTP de exemplo para testes
     *
     * @return array
     */
    public static function httpHeaders()
    {
        return array(
            'json' => array('Content-Type: application/json'),
            'with_auth' => array(
                'Content-Type: application/json',
                'Authorization: Bearer test-token'
            ),
            'binary' => array('Content-Type: application/octet-stream'),
            'custom_user_agent' => array(
                'Content-Type: application/json',
                'User-Agent: Test-Client/1.0'
            )
        );
    }

    /**
     * Configurações de timeout para diferentes tipos de teste
     *
     * @return array
     */
    public static function timeoutConfigurations()
    {
        return array(
            'fast' => array(
                'timeout' => 10,
                'connect_timeout' => 5
            ),
            'normal' => array(
                'timeout' => 30,
                'connect_timeout' => 10
            ),
            'slow' => array(
                'timeout' => 120,
                'connect_timeout' => 30
            ),
            'streaming' => array(
                'timeout' => 300,
                'connect_timeout' => 30
            )
        );
    }
}

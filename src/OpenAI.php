<?php

namespace Ollama;

use Ollama\Exceptions\OllamaException;
use Ollama\Http\HttpClient;
use Ollama\Models\Model;
use Ollama\Models\Message;

/**
 * Cliente para API compatível com OpenAI usando Ollama
 * Implementa os endpoints compatíveis conforme documentação em openai.md
 */
class OpenAI
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $baseUrl URL base do servidor (ex: http://localhost:11434)
     * @param string $apiKey Chave API (obrigatório mas ignorado pelo Ollama)
     * @param array $options Opções adicionais para configuração
     */
    public function __construct($baseUrl = 'http://localhost:11434/v1', $apiKey = 'ollama', array $options = array())
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->httpClient = new HttpClient($this->baseUrl, $options);
        $this->httpClient->setApiToken($apiKey);
    }

    /**
     * Define o token de API
     *
     * @param string $apiKey
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->httpClient->setApiToken($apiKey);
    }

    /**
     * Obtém o token de API
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Chat Completions - Compatível com OpenAI API
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function chatCompletions(array $params, $streamCallback = null)
    {
        $endpoint = '/chat/completions';

        // Valida parâmetros obrigatórios
        if (!isset($params['model'])) {
            throw new OllamaException('O parâmetro "model" é obrigatório');
        }

        $this->parseModelFromParams($params);

        if (!isset($params['messages'])) {
            throw new OllamaException('O parâmetro "messages" é obrigatório');
        }

        if ($streamCallback !== null && isset($params['stream']) && $params['stream']) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }

        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Completions - Compatível com OpenAI API
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function completions(array $params, $streamCallback = null)
    {
        $endpoint = '/completions';

        $this->parseModelFromParams($params);

        // Valida parâmetros obrigatórios
        if (!isset($params['model'])) {
            throw new OllamaException('O parâmetro "model" é obrigatório');
        }

        if (!isset($params['prompt'])) {
            throw new OllamaException('O parâmetro "prompt" é obrigatório');
        }

        if ($streamCallback !== null && isset($params['stream']) && $params['stream']) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }

        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Embeddings - Compatível com OpenAI API
     *
     * @param array $params Parâmetros da requisição
     * @return array
     * @throws OllamaException
     */
    public function embeddings(array $params)
    {
        $endpoint = '/embeddings';

        // Valida parâmetros obrigatórios
        if (!isset($params['model'])) {
            throw new OllamaException('O parâmetro "model" é obrigatório');
        }

        $this->parseModelFromParams($params);

        if (!isset($params['input'])) {
            throw new OllamaException('O parâmetro "input" é obrigatório');
        }

        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Lista modelos disponíveis - Compatível com OpenAI API
     *
     * @return array
     * @throws OllamaException
     */
    public function listModels()
    {
        return $this->httpClient->get('/models');
    }

    /**
     * Obtém informações de um modelo específico - Compatível com OpenAI API
     *
     * @param string $model Nome do modelo
     * @return array
     * @throws OllamaException
     */
    public function retrieveModel($model)
    {
        if ($model instanceof Model) {
            $model = $model->getName();
        }

        if (isset($model['model'])) {
            $model = $model['model'];
        }

        if (isset($model['name'])) {
            $model = $model['name'];
        }

        return $this->httpClient->get('/models/' . urlencode($model));
    }

    /**
     * Métodos auxiliares para criar requisições mais facilmente
     */

    /**
     * Cria uma chat completion simples
     *
     * @param string|Model $model Nome do modelo ou instância de Model
     * @param array $messages Array de mensagens
     * @param array $options Opções adicionais
     * @param callable|null $streamCallback Callback para streaming
     * @return array
     * @throws OllamaException
     */
    public function chat($model, array $messages, array $options = array(), $streamCallback = null)
    {
        $params = array(
            'model' => $model,
            'messages' => $messages
        );

        $this->parseModelFromParams($params);

        // Se for uma instância de Model, mescla os parâmetros
        if ($model instanceof Model) {
            $modelArray = $model->toArray();
            unset($modelArray['model']); // Remove o nome do modelo pois já foi definido
            $params = array_merge($params, $modelArray);
        }

        $params = array_merge($params, $options);

        return $this->chatCompletions($params, $streamCallback);
    }

    /**
     * Cria uma completion simples
     *
     * @param string|Model $model Nome do modelo ou instância de Model
     * @param string $prompt Prompt para completar
     * @param array $options Opções adicionais
     * @param callable|null $streamCallback Callback para streaming
     * @return array
     * @throws OllamaException
     */
    public function complete($model, $prompt, array $options = array(), $streamCallback = null)
    {
        $params = array(
            'model' => $model,
            'prompt' => $prompt
        );

        $this->parseModelFromParams($params);

        // Se for uma instância de Model, mescla os parâmetros
        if ($model instanceof Model) {
            $modelArray = $model->toArray();
            unset($modelArray['model']); // Remove o nome do modelo pois já foi definido
            $params = array_merge($params, $modelArray);
        }

        $params = array_merge($params, $options);

        return $this->completions($params, $streamCallback);
    }

    /**
     * Cria embeddings para texto
     *
     * @param string|Model $model Nome do modelo ou instância de Model
     * @param string|array $input Texto ou array de textos
     * @param array $options Opções adicionais
     * @return array
     * @throws OllamaException
     */
    public function embed($model, $input, array $options = array())
    {
        $params = array(
            'model' => $model,
            'input' => $input
        );

        $this->parseModelFromParams($params);

        $params = array_merge($params, $options);

        return $this->embeddings($params);
    }

    /**
     * Cria uma mensagem de chat
     *
     * @param string $role Papel da mensagem (system, user, assistant)
     * @param string|array $content Conteúdo da mensagem
     * @return array
     */
    public function createMessage($role, $content)
    {
        return array(
            'role' => $role,
            'content' => $content
        );
    }

    /**
     * Cria uma mensagem de sistema
     *
     * @param string $content Conteúdo da mensagem
     * @return array
     */
    public function systemMessage($content)
    {
        return $this->createMessage('system', $content);
    }

    /**
     * Cria uma mensagem de usuário
     *
     * @param string|array $content Conteúdo da mensagem (pode incluir imagens)
     * @return array
     */
    public function userMessage($content)
    {
        return $this->createMessage('user', $content);
    }

    /**
     * Cria uma mensagem de assistente
     *
     * @param string $content Conteúdo da mensagem
     * @return array
     */
    public function assistantMessage($content)
    {
        return $this->createMessage('assistant', $content);
    }

    /**
     * Cria uma mensagem com imagem (para modelos de visão como llava)
     *
     * @param string $text Texto da mensagem
     * @param string $imageUrl URL da imagem ou dados base64
     * @param string $role Papel da mensagem (padrão: user)
     * @return array
     */
    public function imageMessage($text, $imageUrl, $role = 'user')
    {
        return array(
            'role' => $role,
            'content' => array(
                array(
                    'type' => 'text',
                    'text' => $text
                ),
                array(
                    'type' => 'image_url',
                    'image_url' => is_array($imageUrl) ? $imageUrl : array('url' => $imageUrl)
                )
            )
        );
    }

    /**
     * Configura resposta em formato JSON
     *
     * @return array
     */
    public function jsonFormat()
    {
        return array('type' => 'json_object');
    }

    /**
     * Configura opções de streaming
     *
     * @param bool $includeUsage Se deve incluir informações de uso
     * @return array
     */
    public function streamOptions($includeUsage = false)
    {
        return array('include_usage' => $includeUsage);
    }

    /**
     * Obtém o cliente HTTP para uso avançado
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Método para chat com streaming simplificado
     *
     * @param string|Model $model Nome do modelo ou instância de Model
     * @param array $messages Array de mensagens
     * @param callable $callback Função chamada para cada chunk
     * @param array $options Opções adicionais
     * @return void
     * @throws OllamaException
     */
    public function chatStream($model, array $messages, callable $callback, array $options = array())
    {
        $options['stream'] = true;
        $this->chat($model, $messages, $options, $callback);
    }

    /**
     * Método para completion com streaming simplificado
     *
     * @param string|Model $model Nome do modelo ou instância de Model
     * @param string $prompt Prompt para completar
     * @param callable $callback Função chamada para cada chunk
     * @param array $options Opções adicionais
     * @return void
     * @throws OllamaException
     */
    public function completeStream($model, $prompt, callable $callback, array $options = array())
    {
        $options['stream'] = true;
        $this->complete($model, $prompt, $options, $callback);
    }


    /**
     * Método auxiliar para extrair o nome do modelo dos parâmetros
     *
     * @param array $params Parâmetros da requisição
     * @return void
     */
    private function parseModelFromParams(array &$params)
    {
        if (isset($params['model'])) {
            if ($params['model'] instanceof Model) {
                $model = $params['model'];
                $params['model'] = $model->getName();

                foreach ($model->getParameters() as $key => $value) {
                    if (!isset($params[$key])) {
                        $params[$key] = $value;
                    }
                }

                foreach ($model->getOptions() as $key => $value) {
                    if (!isset($params[$key])) {
                        $params[$key] = $value;
                    }
                }
            }
        }
    }
}

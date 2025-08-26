<?php

namespace Vluzrmos\Ollama;

use Vluzrmos\Ollama\Exceptions\OllamaException;
use Vluzrmos\Ollama\Exceptions\RequiredParameterException;
use Vluzrmos\Ollama\Http\HttpClient;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Models\MessageFormatter;
use Vluzrmos\Ollama\Models\OpenAIMessageFormatter;

/**
 * Client for OpenAI-compatible API using Ollama
 * Implements compatible endpoints as per openai.md documentation
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
     * @var OpenAIMessageFormatter
     */
    private $messageFormatter;

    /**
     * @param string $baseUrl Base server URL (e.g. http://localhost:11434)
     * @param string $apiKey API key (required but ignored by Ollama)
     * @param array $options Additional configuration options
     */
    public function __construct($baseUrl = 'http://localhost:11434/v1', $apiKey = 'ollama', array $options = array(), MessageFormatter $messageFormatter = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->httpClient = new HttpClient($this->baseUrl, $options);
        $this->httpClient->setApiToken($apiKey);
        $this->messageFormatter = $messageFormatter;
    }

    /**
     * Sets the API token
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
     * Gets the API token
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

        $this->parseModelFromParams($params);

        if (!isset($params['messages'])) {
            throw RequiredParameterException::parameter('messages');
        }

        $params['messages'] = $this->formatMessages($params['messages']);

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

        if (!isset($params['prompt'])) {
            throw RequiredParameterException::parameter('prompt');
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

        $this->parseModelFromParams($params);

        if (!isset($params['input'])) {
            throw RequiredParameterException::parameter('input');
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

    public function formatMessages(array $messages, MessageFormatter $formatter = null)
    {
        $formatter = $formatter ?: $this->getMessageFormatter();

        foreach ($messages as $i => $message) {
            if ($message instanceof Message) {
                $messages[$i] = $formatter->format($message);
            }
        }

        return $messages;
    }

    public function getMessageFormatter()
    {
        if (!$this->messageFormatter) {
            $this->setMessageFormatter(new OpenAIMessageFormatter());
        }

        return $this->messageFormatter;
    }

    public function setMessageFormatter(MessageFormatter $messageFormatter)
    {
        $this->messageFormatter = $messageFormatter;

        return $this;
    }
    /**
     * Creates a simple chat completion
     *
     * @param string|Model $model Model name or Model instance
     * @param array $messages Array of messages
     * @param array $options Additional options
     * @param callable|null $streamCallback Callback para streaming
     * @return array
     * @throws OllamaException
     */
    public function chat($model, array $messages, array $options = array(), $streamCallback = null)
    {
        $convertedMessages = $this->formatMessages($messages);

        $params = array(
            'model' => $model,
            'messages' => $convertedMessages
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
     * Helper method to extract model name from parameters
     *
     * @param array $params Request parameters
     * @return void
     */
    private function parseModelFromParams(array &$params)
    {
        // Valida parâmetros obrigatórios
        if (!isset($params['model'])) {
            throw RequiredParameterException::parameter('model');
        }

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

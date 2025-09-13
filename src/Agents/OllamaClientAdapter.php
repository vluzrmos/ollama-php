<?php

namespace Vluzrmos\Ollama\Agents;

use Vluzrmos\Ollama\Ollama;
use Vluzrmos\Ollama\Models\Message;

/**
 * Adapter for Ollama client to provide unified interface
 */
class OllamaClientAdapter implements ClientAdapterInterface
{
    /**
     * @var Ollama
     */
    private $client;

    /**
     * @param Ollama $client
     */
    public function __construct(Ollama $client)
    {
        $this->client = $client;
    }

    /**
     * Send a chat completion request to Ollama client
     *
     * @param string $model Model name to use
     * @param array $messages Array of messages in conversation
     * @param array $tools Array of available tools (optional)
     * @param array $options Additional options for the request
     * @return mixed Response from the client
     */
    public function chatCompletion($model, array $messages, array $tools = [], array $options = [])
    {
        $params = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        // Add tools if provided
        if (!empty($tools)) {
            $params['tools'] = $tools;
        }

        return $this->client->chat($params);
    }

    /**
     * Get the underlying Ollama client instance
     *
     * @return Ollama
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the type of client adapter
     *
     * @return string
     */
    public function getClientType()
    {
        return 'ollama';
    }
}
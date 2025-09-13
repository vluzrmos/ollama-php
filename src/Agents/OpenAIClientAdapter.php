<?php

namespace Vluzrmos\Ollama\Agents;

use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Message;

/**
 * Adapter for OpenAI client to provide unified interface
 */
class OpenAIClientAdapter implements ClientAdapterInterface
{
    /**
     * @var OpenAI
     */
    private $client;

    /**
     * @param OpenAI $client
     */
    public function __construct(OpenAI $client)
    {
        $this->client = $client;
    }

    /**
     * Send a chat completion request to OpenAI client
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

        return $this->client->chatCompletions($params);
    }

    /**
     * Get the underlying OpenAI client instance
     *
     * @return OpenAI
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
        return 'openai';
    }
}
<?php

namespace Vluzrmos\Ollama\Agents;

/**
 * Interface for client adapters that provide unified access to different AI clients
 */
interface ClientAdapterInterface
{
    /**
     * Send a chat completion request to the underlying client
     *
     * @param string $model Model name to use
     * @param array $messages Array of messages in conversation
     * @param array $tools Array of available tools (optional)
     * @param array $options Additional options for the request
     * @return mixed Response from the client
     */
    public function chatCompletion($model, array $messages, array $tools = [], array $options = []);

    /**
     * Get the underlying client instance
     *
     * @return mixed The actual client (OpenAI or Ollama instance)
     */
    public function getClient();

    /**
     * Get the type of client adapter
     *
     * @return string Client type identifier
     */
    public function getClientType();
}
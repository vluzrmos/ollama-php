<?php

namespace Vluzrmos\Ollama\Chat;

use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\OpenAI;

class OpenAIClientAdapter implements ClientAdapter
{
    protected $client;

    public function __construct(OpenAI $client)
    {
        $this->client = $client;
    }

    public function chatCompletions(Model $model, array $messages, array $params = [])
    {
        return $this->client->chatCompletions(
            array_merge([
                'model' => $model,
                'messages' => $messages,
            ], $params)
        );
    }

    public function prompt(Model $model, $prompt, array $params = []) {
        return $this->client->completions(
            array_merge([
                'model' => $model,
                'prompt' => $prompt,
            ], $params)
        );
    }

    public function stream(Model $model, array $messages, \Closure $calllback, array $params = []) {
        return $this->client->chatCompletions(
            array_merge([
                'model' => $model,
                'messages' => $messages,
                'stream' => true,
            ], $params),
            $calllback
        );
    }
}

<?php

namespace Vluzrmos\Ollama\Chat;

use Closure;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Models\ResponseMessage;

interface ClientAdapter
{
    /**
     * Gets chat completions from the model.
     * 
     * @param Model $model
     * @param array $messages
     * @param array $params
     * @return ResponseMessage Last response from the model
     */
    public function chatCompletions(Model $model, array $messages, array $params = []);

    /**
     * Gets completions for a single prompt from the model.
     *
     * @param Model $model
     * @param string $prompt
     * @param array $params
     * @return ResponseMessage Last response from the model
     */
    public function prompt(Model $model, $prompt, array $params = []);

    /**
     * Streams chat completions from the model.
     *
     * @param Model $model
     * @param array $messages
     * @param Closure $calllback
     * @param array $params
     * @return void
     */
    public function stream(Model $model, array $messages, Closure $calllback, array $params = []);
}

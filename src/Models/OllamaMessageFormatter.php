<?php

namespace Vluzrmos\Ollama\Models;

class OllamaMessageFormatter implements MessageFormatter
{
    public function format(Message $message)
    {
        $data = [
            'role' => $message->role,
            'content' => $message->content,
        ];

        if ($message->images !== null) {
            $data['images'] = $message->images;
        }

        if ($message->toolCalls !== null) {
            $data['tool_calls'] = $message->toolCalls;
        }

        if ($message->toolName !== null) {
            $data['tool_name'] = $message->toolName;
        }

        if ($message->thinking !== null) {
            $data['thinking'] = $message->thinking;
        }

        if ($message->toolCallId !== null) {
            $data['tool_call_id'] = $message->toolCallId;
        }

        return $data;
    }
}

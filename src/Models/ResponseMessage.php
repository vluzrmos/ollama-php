<?php

namespace Vluzrmos\Ollama\Models;

/**
 * Represents a response containing messages
 */
class ResponseMessage extends ResponseModel
{
    public function getRawMessages()
    {
        $messages = isset($this->body['messages']) ? $this->body['messages'] : null;

        if (is_array($messages)) {
            return $messages;
        }

        if (isset($this->body['choices'][0]['message'])) {
            $messages = [];

            foreach ($this->body['choices'] as $choice) {
                $messages[] = $choice['message'];
            }
        }

        return $messages ?: [];
    }

    public function getMessages()
    {
        $messages = [];

        foreach ($this->getRawMessages() as $message) {
            $messages = Message::fromArray($message);
        }

        return $messages;
    }
}

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

    /**
     * Get messages as Message instances
     * 
     * @return Message[]
     */
    public function getMessages()
    {
        $messages = [];

        foreach ($this->getRawMessages() as $message) {
            $messages[] = Message::fromArray($message);
        }

        return $messages;
    }

    public function hasToolCalls()
    {
        foreach ($this->getMessages() as $message) {
            if ($message->hasToolCalls()) {
                return true;
            }
        }

        return false;
    }

    public function getToolCalls()
    {
        $toolCalls = [];

        foreach ($this->getMessages() as $message) {
            if ($message->hasToolCalls()) {
                $toolCalls = array_merge($toolCalls, (array) $message->toolCalls);
            }
        }

        return $toolCalls;
    }
}

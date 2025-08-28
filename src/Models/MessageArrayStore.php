<?php

namespace Vluzrmos\Ollama\Models;

class MessageArrayStore implements MessageStore
{
    /**
     * @var Message[]
     */
    protected $messages = [];

    public function add(Message $message)
    {
        $this->messages[] = $message;
    }

    public function remove($index)
    {
        if (isset($this->messages[$index])) {
            array_splice($this->messages, $index, 1);
        }
    }

    public function all()
    {
        return $this->messages;
    }

    public function clear()
    {
        $this->messages = [];
    }

    public function count()
    {
        return count($this->messages);
    }

    public function get($index)
    {
        return $this->messages[$index] ?? null;
    }

    public function set($index, Message $message)
    {
        $this->messages[$index] = $message;
    }

    public function first()
    {
        foreach ($this->messages as $message) {
            return $message;
        }
    }

    public function last()
    {
        $last = end($this->messages);
        reset($this->messages);

        return $last ?: null;
    }
}

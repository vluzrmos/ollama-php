<?php

namespace Vluzrmos\Ollama\Models;

interface MessageFormatter
{
    /**
     * Format a message for API request
     *
     * @param Message $message
     * @return array
     */
    public function format(Message $message);
}

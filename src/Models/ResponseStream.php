<?php

namespace Vluzrmos\Ollama\Models;

class ResponseStream extends ResponseModel
{
    public function getContent()
    {
        $content = isset($this->body['message']['content']) ? $this->body['message']['content'] : null;

        if ($content !== null) {
            return $content;
        }

        if (isset($this->body['choices'][0]['delta']['content'])) {
            $content = '';

            foreach ($this->body['choices'] as $choice) {
                $content .= $choice['delta']['content'];
            }

            return $content;
        }

        return null;
    }

    public function isDone()
    {
        if (isset($this->body['done'])) {
            return $this->body['done'];
        }

        $choices = isset($this->body['choices']) ? $this->body['choices'] : null;

        foreach ($choices as $choice) {
            if (!empty($choice['finish_reason'])) {
                return true;
            }
        }

        return false;
    }
}

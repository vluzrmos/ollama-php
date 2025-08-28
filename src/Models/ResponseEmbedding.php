<?php

namespace Vluzrmos\Ollama\Models;

/**
 * Represents a response containing embeddings
 */
class ResponseEmbedding extends ResponseModel
{
    public function getEmbeddings()
    {
        $embeddings = isset($this->body['embeddings']) ? $this->body['embeddings'] : null;

        if (is_array($embeddings)) {
            return $embeddings;
        }

        if (isset($this->body['data'][0]['embedding'])) {
            $embeddings = [];

            foreach ($this->body['data'] as $item) {
                $embeddings[] = $item['embedding'];
            }
        }
    }
}

<?php

namespace Vluzrmos\Ollama\Models;

use ArrayAccess;

/**
 * Represents a generic response from the API.
 */
class Response implements ArrayAccess
{
    protected $body;

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function getAnyAttribute($keys)
    {
        $keys = is_array($keys) ? $keys : [$keys];

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->body)) {
                return $this->body[$key];
            }
        }

        return null;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        return null;
    }

    public function __set($key, $value)
    {
        $this->body[$key] = $value;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->body);
    }

    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->body)) {
            return $this->body[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->body[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->body)) {
            unset($this->body[$offset]);
        }
    }

    public function toArray()
    {
        return $this->body;
    }
}

<?php

namespace Vluzrmos\Ollama\Models;

interface MessageStore
{
    public function add(Message $message);

    public function remove($index);

    public function all();

    public function clear();

    public function count();

    public function get($index);

    public function set($index, Message $message);

    public function first();

    public function last();
}

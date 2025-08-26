<?php

namespace Vluzrmos\Ollama\Exceptions;

use RuntimeException;
use Throwable;

class RequiredParameterException extends RuntimeException
{
    protected $parameter = null;

    public static function parameter($name, $code = 0, Throwable $previous = null)
    {
        $instance = new self("parameter \"{$name}\" is required", $code, $previous);

        $instance->parameter = $name;

        return $instance;
    }

    public function getParameterName()
    {
        return $this->parameter;
    }
}

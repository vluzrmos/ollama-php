<?php

namespace Vluzrmos\Ollama\Models;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Represents a response from a Model (chat, generate, completions, chat completions, embedding...).
 */
class ResponseModel extends Response
{
    public function getModel()
    {
        return isset($this->body['model']) ? $this->body['model'] : null;
    }

    public function getCreatedAt()
    {
        $created = $this->created_at;

        $timezone = new DateTimeZone('UTC');

        if (is_string($created)) {
            $created = preg_replace_callback(
                '/\.(\d+)(Z|[\+\-]\d{2}:\d{2})/',
                function ($m) {
                    $frac = str_pad(substr($m[1], 0, 6), 6, "0"); // corta ou preenche com zeros
                    return "." . $frac . $m[2];
                },
                $created
            );

            return DateTimeImmutable::createFromFormat("Y-m-d\TH:i:s.uP", $created, $timezone);
        }

        $created = $this->created;

        if (is_int($created) || is_numeric($created)) {
            return new DateTimeImmutable("@{$created}", $timezone);
        }

        return null;
    }
}

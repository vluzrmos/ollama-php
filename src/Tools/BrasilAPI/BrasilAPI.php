<?php

namespace Vluzrmos\Ollama\Tools\BrasilAPI;

use Vluzrmos\Ollama\Http\HttpClient;

class BrasilAPI extends HttpClient
{
    public function __construct($baseUrl = 'https://brasilapi.com.br/api', array $options = [])
    {
        parent::__construct($baseUrl, $options);
    }

}

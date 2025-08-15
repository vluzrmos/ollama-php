<?php

namespace Ollama\Exceptions;

use Exception;

/**
 * Exceção base para erros da API do Ollama
 */
class OllamaException extends Exception
{
    /**
     * @var array|null
     */
    protected $responseData;

    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param array|null $responseData
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, array $responseData = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseData = $responseData;
    }

    /**
     * Obtém os dados da resposta que causou a exceção
     *
     * @return array|null
     */
    public function getResponseData()
    {
        return $this->responseData;
    }
}

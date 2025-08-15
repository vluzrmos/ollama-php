<?php

namespace Vluzrmos\Ollama\Utils;

/**
 * Utilitários para validação de dados
 */
class Validator
{
    /**
     * Valida se um modelo está no formato correto
     *
     * @param string $model
     * @return bool
     */
    public static function isValidModelName($model)
    {
        if (empty($model) || !is_string($model)) {
            return false;
        }

        // Formato: [namespace/]model[:tag]
        // Exemplos: llama3.2, orca-mini:3b-q8_0, example/model:latest
        return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*(?:\/[a-zA-Z0-9][a-zA-Z0-9._-]*)?(?::[a-zA-Z0-9][a-zA-Z0-9._-]*)?$/', $model);
    }

    /**
     * Valida uma mensagem de chat
     *
     * @param array $message
     * @return bool
     */
    public static function isValidMessage(array $message)
    {
        if (!isset($message['role']) || !isset($message['content'])) {
            return false;
        }

        $validRoles = array('system', 'user', 'assistant', 'tool');
        return in_array($message['role'], $validRoles);
    }

    /**
     * Valida um array de mensagens
     *
     * @param array $messages
     * @return bool
     */
    public static function isValidMessages(array $messages)
    {
        if (empty($messages)) {
            return false;
        }

        foreach ($messages as $message) {
            if (!is_array($message) || !self::isValidMessage($message)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida parâmetros de uma tool
     *
     * @param array $tool
     * @return bool
     */
    public static function isValidTool(array $tool)
    {
        if (!isset($tool['type']) || $tool['type'] !== 'function') {
            return false;
        }

        if (!isset($tool['function']) || !is_array($tool['function'])) {
            return false;
        }

        $function = $tool['function'];
        return isset($function['name']) && isset($function['description']);
    }

    /**
     * Valida um digest SHA256
     *
     * @param string $digest
     * @return bool
     */
    public static function isValidSha256($digest)
    {
        return preg_match('/^sha256:[a-f0-9]{64}$/', $digest);
    }

    /**
     * Valida opções do modelo
     *
     * @param array $options
     * @return bool
     */
    public static function isValidModelOptions(array $options)
    {
        $validOptions = array(
            'num_keep', 'seed', 'num_predict', 'top_k', 'top_p', 'min_p',
            'typical_p', 'repeat_last_n', 'temperature', 'repeat_penalty',
            'presence_penalty', 'frequency_penalty', 'penalize_newline',
            'stop', 'numa', 'num_ctx', 'num_batch', 'num_gpu', 'main_gpu',
            'use_mmap', 'num_thread'
        );

        foreach ($options as $key => $value) {
            if (!in_array($key, $validOptions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sanitiza e valida um nome de modelo
     *
     * @param string $model
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function sanitizeModelName($model)
    {
        if (!self::isValidModelName($model)) {
            throw new \InvalidArgumentException('Nome de modelo inválido: ' . $model);
        }

        return trim($model);
    }
}

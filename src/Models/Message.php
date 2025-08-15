<?php

namespace Vluzrmos\Ollama\Models;

/**
 * Representa uma mensagem no chat
 */
class Message
{
    /**
     * @var string
     */
    public $role;

    /**
     * @var string
     */
    public $content;

    /**
     * @var array|null
     */
    public $images;

    /**
     * @var array|null
     */
    public $toolCalls;

    /**
     * @var string|null
     */
    public $toolName;

    /**
     * @var string|null
     */
    public $thinking;

    /**
     * @param string $role
     * @param string $content
     * @param array|null $images
     */
    public function __construct($role, $content, array $images = null)
    {
        $this->role = $role;
        $this->content = $content;
        $this->images = $images;
    }

    /**
     * Cria uma mensagem do usuário
     *
     * @param string $content
     * @param array|null $images
     * @return Message
     */
    public static function user($content, array $images = null)
    {
        return new self('user', $content, $images);
    }

    /**
     * Cria uma mensagem do assistente
     *
     * @param string $content
     * @return Message
     */
    public static function assistant($content)
    {
        return new self('assistant', $content);
    }

    /**
     * Cria uma mensagem do sistema
     *
     * @param string $content
     * @return Message
     */
    public static function system($content)
    {
        return new self('system', $content);
    }

    /**
     * Cria uma mensagem de tool
     *
     * @param string $content
     * @param string $toolName
     * @return Message
     */
    public static function tool($content, $toolName)
    {
        $message = new self('tool', $content);
        $message->toolName = $toolName;
        return $message;
    }

    /**
     * Converte a mensagem para array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            'role' => $this->role,
            'content' => $this->content
        );

        if ($this->images !== null) {
            $data['images'] = $this->images;
        }

        if ($this->toolCalls !== null) {
            $data['tool_calls'] = $this->toolCalls;
        }

        if ($this->toolName !== null) {
            $data['tool_name'] = $this->toolName;
        }

        if ($this->thinking !== null) {
            $data['thinking'] = $this->thinking;
        }

        return $data;
    }
}

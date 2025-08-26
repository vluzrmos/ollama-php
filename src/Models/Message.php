<?php

namespace Vluzrmos\Ollama\Models;

use ArrayAccess;

/**
 * Representa uma mensagem no chat
 */
class Message implements ArrayAccess
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
     * Cria uma mensagem com imagem (para modelos de visão como llava)
     *
     * @param string $text Texto da mensagem
     * @param string $imageUrl URL da imagem ou dados base64
     * @param string $role Papel da mensagem (padrão: user)
     * @return Message
     */
    public static function image($text, $imageUrl, $role = 'user')
    {
        $content = $text;

        $message = new self($role, $content);

        $message->images = is_array($imageUrl) ? $imageUrl : [$imageUrl];

        return $message;
    }

    public static function fromArray(array $message)
    {
        $role = isset($message['role']) ? $message['role'] : null;
        $content = isset($message['content']) ? $message['content'] : null;
        $images = isset($message['images']) ? $message['images'] : null;

        if ($role === null || $content === null) {
            throw new \InvalidArgumentException("Array must contain 'role' and 'content' keys.");
        }

        if (is_array($content) && isset($content[0]['type'])) {
            foreach ($content as $part) {
                if ($part['type'] === 'text') {
                    $content = $part['text'];
                } elseif ($part['type'] === 'image_url' && isset($part['images'])) {
                    $images = array_merge($images, is_array($part['images']) ? $part['images'] : [$part['images']]);
                }
            }
        }

        return new self($role, $content, $images);
    }

    protected function getAcessibleProperties()
    {
        return [
            'role',
            'content',
            'images',
            'toolCalls',
            'toolName',
            'thinking'
        ];
    }

    public function offsetGet($offset)
    {
        $props = $this->getAcessibleProperties();

        if (in_array($offset, $props, true)) {
            return $this->$offset;
        }

        return null;
    }

    public function offsetExists($offset)
    {
        return in_array($offset, $this->getAcessibleProperties(), true);
    }

    public function offsetSet($offset, $value)
    {
        if (in_array($offset, $this->getAcessibleProperties(), true)) {
            $this->$offset = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (in_array($offset, $this->getAcessibleProperties(), true)) {
            $this->$offset = null;
        }
    }
}

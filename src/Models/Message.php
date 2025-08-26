<?php

namespace Vluzrmos\Ollama\Models;

use ArrayAccess;

/**
 * Represents a chat message
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
    public $toolCallId;

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
     * Creates a user message
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
     * Creates an assistant message
     *
     * @param string $content
     * @return Message
     */
    public static function assistant($content)
    {
        return new self('assistant', $content);
    }

    /**
     * Creates a system message
     *
     * @param string $content
     * @return Message
     */
    public static function system($content)
    {
        return new self('system', $content);
    }

    /**
     * Creates a tool message
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
     * Creates a message with image (for vision models like llava)
     *
     * @param string $text Message text
     * @param string $imageUrl Image URL or base64 data
     * @param string $role Message role (default: user)
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
        $thinking = isset($message['thinking']) ? $message['thinking'] : null;
        $toolName = isset($message['tool_name']) ? $message['tool_name'] : (isset($message['toolName']) ? $message['toolName'] : null);
        $toolCallId = isset($message['tool_call_id']) ? $message['tool_call_id'] : (isset($message['toolCallId']) ? $message['toolCallId'] : null);
        
        if ($role === null || $content === null) {
            throw new \InvalidArgumentException("Array must contain 'role' and 'content' keys.");
        }

        if (is_array($content) && isset($content[0]['type'])) {
            foreach ($content as $part) {
                if ($part['type'] === 'text') {
                    $content = $part['text'];
                } elseif ($part['type'] === 'image_url' && isset($part['image_url'])) {
                    $images = array_merge($images, is_array($part['image_url']) ? $part['image_url'] : [$part['image_url']]);
                }
            }
        }

        $instance = new self($role, $content, $images);

        if ($thinking !== null) {
            $instance->thinking = $thinking;
        }

        if ($toolName !== null) {
            $instance->toolName = $toolName;
        }

        if ($toolCallId !== null) {
            $instance->toolCallId = $toolCallId;
        }

        return $instance;
    }

    protected function getAcessibleProperties()
    {
        return [
            'role',
            'content',
            'images',
            'toolCalls',
            'toolName',
            'thinking',
            'toolCallId',
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

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
     * @var array
     */
    public $__attributes = [];

    /**
     * @param string $role
     * @param string $content
     * @param array $attributes Additional attributes
     * @param array|null $images
     */
    public function __construct($role, $content, array $images = null, array $attributes = [])
    {
        $this->role = $role;
        $this->content = $content;
        $this->images = $images;
        $this->__attributes = $attributes;
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

    public function toArray()
    {
        $data = [];

        if ($this->role !== null) {
            $data['role'] = $this->role;
        }

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

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

        if ($this->toolCallId !== null) {
            $data['tool_call_id'] = $this->toolCallId;
        }

        foreach ($this->__attributes as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public static function fromArray(array $message)
    {
        $role = isset($message['role']) ? $message['role'] : null;
        $content = isset($message['content']) ? $message['content'] : null;
        $images = isset($message['images']) ? $message['images'] : null;
        $thinking = isset($message['thinking']) ? $message['thinking'] : null;
        $toolName = isset($message['tool_name']) ? $message['tool_name'] : (isset($message['toolName']) ? $message['toolName'] : null);
        $toolCallId = isset($message['tool_call_id']) ? $message['tool_call_id'] : (isset($message['toolCallId']) ? $message['toolCallId'] : null);

        $toolCalls = isset($message['tool_calls']) ? $message['tool_calls'] : (isset($message['toolCalls']) ? $message['toolCalls'] : null);

        if ($role === null || $content === null) {
            throw new \InvalidArgumentException("Array must contain 'role' and 'content' keys.");
        }

        if (is_array($content) && isset(array_values($content)[0]['type'])) {
            $texts = [];

            foreach ($content as $part) {
                if ($part['type'] === 'text') {
                    $texts[] = $part['text'];
                } elseif ($part['type'] === 'image_url' && isset($part['image_url'])) {
                    $images = array_merge((array) $images, (array) $part['image_url']);
                }
            }

            $content = implode("\n", $texts);
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

        if ($toolCalls !== null) {
            $instance->toolCalls = $toolCalls;
        }

        foreach ($message as $key => $value) {
            if (!in_array($key, ['role', 'content', 'images', 'thinking', 'tool_name', 'toolCalls', 'toolCallName', 'tool_call_id', 'toolCallId'])) {
                $instance->params[$key] = $value;
            }
        }

        return $instance;
    }

    protected function getAcessibleProperties()
    {
        $properties = [
            'role',
            'content',
            'images',
            'toolCalls',
            'toolName',
            'thinking',
            'toolCallId',
        ];

        return array_merge($properties, array_keys($this->sluggedProperties()));
    }

    public function offsetGet($offset)
    {
        $offset = $this->getSluggedPropertyOriginalName($offset);

        $props = $this->getAcessibleProperties();

        if (in_array($offset, $props, true)) {
            return $this->$offset;
        }

        if (array_key_exists($offset, $this->__attributes)) {
            return $this->__attributes[$offset];
        }

        return null;
    }

    public function offsetExists($offset)
    {
        $offset = $this->getSluggedPropertyOriginalName($offset);

        return in_array($offset, $this->getAcessibleProperties(), true) || array_key_exists($offset, $this->__attributes);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null || $offset === '') {
            return;
        }

        $offset = $this->getSluggedPropertyOriginalName($offset);

        if (in_array($offset, $this->getAcessibleProperties(), true)) {
            $this->$offset = $value;

            return;
        }

        $this->__attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $offset = $this->getSluggedPropertyOriginalName($offset);

        if (in_array($offset, $this->getAcessibleProperties(), true)) {
            $this->$offset = null;
        }

        if (array_key_exists($offset, $this->__attributes)) {
            unset($this->__attributes[$offset]);
        }
    }

    protected function sluggedProperties()
    {
        return [
            'tool_calls' => 'toolCalls',
            'tool_name' => 'toolName',
            'tool_call_id' => 'toolCallId',
        ];
    }

    public function getSluggedPropertyOriginalName($key)
    {
        $slugs = $this->sluggedProperties();

        if (array_key_exists($key, $slugs)) {
            return $slugs[$key];
        }

        return $key;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function hasToolCalls()
    {
        return is_array($this->toolCalls) && !empty($this->toolCalls);
    }
}

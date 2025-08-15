<?php

namespace Ollama\Tests;

use PHPUnit_Framework_TestCase;
use Ollama\Models\Message;

class MessageTest extends PHPUnit_Framework_TestCase
{
    public function testUserMessage()
    {
        $message = Message::user('Hello world');
        
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello world', $message->content);
        
        $array = $message->toArray();
        $this->assertEquals('user', $array['role']);
        $this->assertEquals('Hello world', $array['content']);
    }

    public function testAssistantMessage()
    {
        $message = Message::assistant('Hi there!');
        
        $this->assertEquals('assistant', $message->role);
        $this->assertEquals('Hi there!', $message->content);
    }

    public function testSystemMessage()
    {
        $message = Message::system('You are a helpful assistant.');
        
        $this->assertEquals('system', $message->role);
        $this->assertEquals('You are a helpful assistant.', $message->content);
    }

    public function testUserMessageWithImages()
    {
        $images = array('base64encodedimage1', 'base64encodedimage2');
        $message = Message::user('What is in these images?', $images);
        
        $this->assertEquals($images, $message->images);
        
        $array = $message->toArray();
        $this->assertEquals($images, $array['images']);
    }

    public function testToolMessage()
    {
        $message = Message::tool('Current temperature is 23°C', 'get_weather');
        
        $this->assertEquals('tool', $message->role);
        $this->assertEquals('Current temperature is 23°C', $message->content);
        $this->assertEquals('get_weather', $message->toolName);
        
        $array = $message->toArray();
        $this->assertEquals('get_weather', $array['tool_name']);
    }
}

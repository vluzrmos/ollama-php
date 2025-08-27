<?php

use Vluzrmos\Ollama\Models\Message;

class MessageTest extends TestCase
{
    public function testMessageConstruction()
    {
        $message = new Message('user', 'Hello world');
        
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello world', $message->content);
        $this->assertNull($message->images);
    }

    public function testMessageConstructionWithImages()
    {
        $images = ['data:image/jpeg;base64,abc123'];
        $message = new Message('user', 'Look at this image', $images);
        
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Look at this image', $message->content);
        $this->assertEquals($images, $message->images);
    }

    public function testUserMessageFactory()
    {
        $message = Message::user('Hello');
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Models\\Message', $message);
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello', $message->content);
    }

    public function testUserMessageFactoryWithImages()
    {
        $images = ['data:image/jpeg;base64,abc123'];
        $message = Message::user('Look at this', $images);
        
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Look at this', $message->content);
        $this->assertEquals($images, $message->images);
    }

    public function testAssistantMessageFactory()
    {
        $message = Message::assistant('Hello there!');
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Models\\Message', $message);
        $this->assertEquals('assistant', $message->role);
        $this->assertEquals('Hello there!', $message->content);
    }

    public function testSystemMessageFactory()
    {
        $message = Message::system('You are a helpful assistant');
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Models\\Message', $message);
        $this->assertEquals('system', $message->role);
        $this->assertEquals('You are a helpful assistant', $message->content);
    }

    public function testToolMessageFactory()
    {
        $message = Message::tool('Result from tool', 'calculator');
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Models\\Message', $message);
        $this->assertEquals('tool', $message->role);
        $this->assertEquals('Result from tool', $message->content);
        $this->assertEquals('calculator', $message->toolName);
    }

    public function testImageMessageFactory()
    {
        $imageUrl = 'data:image/jpeg;base64,abc123';
        $message = Message::image('What do you see?', $imageUrl);
        
        $this->assertEquals('user', $message->role);
        $this->assertEquals('What do you see?', $message->content);
        $this->assertEquals([$imageUrl], $message->images);
    }

    public function testImageMessageFactoryWithCustomRole()
    {
        $imageUrl = 'data:image/jpeg;base64,abc123';
        $message = Message::image('What do you see?', $imageUrl, 'assistant');
        
        $this->assertEquals('assistant', $message->role);
        $this->assertEquals('What do you see?', $message->content);
        $this->assertEquals([$imageUrl], $message->images);
    }

    public function testImageMessageFactoryWithArrayOfImages()
    {
        $images = ['image1.jpg', 'image2.png'];
        $message = Message::image('Multiple images', $images);
        
        $this->assertEquals($images, $message->images);
    }

    public function testFromArrayBasic()
    {
        $array = [
            'role' => 'user',
            'content' => 'Hello world'
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello world', $message->content);
    }

    public function testFromArrayWithImages()
    {
        $array = [
            'role' => 'user',
            'content' => 'Look at this',
            'images' => ['image1.jpg', 'image2.png']
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals(['image1.jpg', 'image2.png'], $message->images);
    }

    public function testFromArrayWithComplexContent()
    {
        $array = [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'Hello'],
                ['type' => 'image_url', 'image_url' => 'image1.jpg']
            ]
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals('Hello', $message->content);
        $this->assertContains('image1.jpg', $message->images);
    }

    public function testFromArrayWithToolData()
    {
        $array = [
            'role' => 'tool',
            'content' => 'Tool result',
            'tool_name' => 'calculator'
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals('tool', $message->role);
        $this->assertEquals('Tool result', $message->content);
        $this->assertEquals('calculator', $message->toolName);
    }

    public function testFromArrayWithThinking()
    {
        $array = [
            'role' => 'assistant',
            'content' => 'Response',
            'thinking' => 'Internal thoughts'
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals('Internal thoughts', $message->thinking);
    }

    public function testFromArrayMissingRole()
    {
        $array = ['content' => 'Hello'];
        
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("Array must contain 'role' and 'content' keys.");
        
        Message::fromArray($array);
    }

    public function testFromArrayMissingContent()
    {
        $array = ['role' => 'user'];
        
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("Array must contain 'role' and 'content' keys.");
        
        Message::fromArray($array);
    }

    public function testArrayAccess()
    {
        $message = new Message('user', 'Hello world');
        
        // Test offsetGet
        $this->assertEquals('user', $message['role']);
        $this->assertEquals('Hello world', $message['content']);
        
        // Test offsetExists
        $this->assertTrue(isset($message['role']));
        $this->assertTrue(isset($message['content']));
        $this->assertFalse(isset($message['nonexistent']));
        
        // Test offsetSet
        $message['thinking'] = 'Some thoughts';
        $this->assertEquals('Some thoughts', $message->thinking);
        
        // Test offsetUnset
        unset($message['thinking']);
        $this->assertNull($message->thinking);
    }

    public function testArrayAccessWithInvalidOffset()
    {
        $message = new Message('user', 'Hello');
        
        // Invalid properties should return null
        $this->assertNull($message['invalid_property']);
        
        // Setting invalid properties should be ignored
        $message['invalid_property'] = 'value';
        $this->assertNull($message['invalid_property']);
    }
}

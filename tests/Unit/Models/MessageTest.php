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
    }

    public function testArrayAccessWithAdditionalAttributes()
    {
        $message = new Message('user', 'Hello', null, ['custom_attr' => 'custom_value']);
        
        $message->custom_attr2 = 'another_value';

        // Invalid properties should return null
        $this->assertNull($message['attr_doest_exists']);

        $this->assertEquals('custom_value', $message['custom_attr']);

        $messageArray = $message->toArray();

        $this->assertArrayHasKey('custom_attr', $messageArray);
        $this->assertArrayHasKey('custom_attr2', $messageArray);
        $this->assertEquals('custom_value', $messageArray['custom_attr']);
        $this->assertEquals('another_value', $messageArray['custom_attr2']);
    }

    public function testSluggedProperties()
    {
        $array = [
            'role' => 'user',
            'content' => 'Hello world',
            'tool_name' => 'calculator',
            'tool_call_id' => '12345'
        ];
        
        $message = Message::fromArray($array);
        
        $message->tool_calls = [['name' => 'calc', 'arguments' => '2+2']];
        
        $this->assertEquals('calculator', $message->toolName);
        $this->assertEquals('12345', $message->toolCallId);
        $this->assertEquals('calculator', $message['tool_name']);
        $this->assertEquals('12345', $message['tool_call_id']);
        $this->assertEquals('calculator', $message->tool_name);
        $this->assertEquals('12345', $message->tool_call_id);
        $this->assertEquals([['name' => 'calc', 'arguments' => '2+2']], $message->tool_calls);
        $this->assertEquals([['name' => 'calc', 'arguments' => '2+2']], $message['tool_calls']);
        $this->assertEquals([['name' => 'calc', 'arguments' => '2+2']], $message->toolCalls);

        $message->toolCalls = [['name' => 'calc2', 'arguments' => '3+3']];

        $this->assertEquals([['name' => 'calc2', 'arguments' => '3+3']], $message->tool_calls);
        $this->assertEquals([['name' => 'calc2', 'arguments' => '3+3']], $message['tool_calls']);
        $this->assertEquals([['name' => 'calc2', 'arguments' => '3+3']], $message->toolCalls);
        $this->assertEquals([['name' => 'calc2', 'arguments' => '3+3']], $message['toolCalls']);
    }

    public function testToolCallsFromArray()
    {
        $array = [
            'role' => 'assistant',
            'content' => 'Here is the result',
            'tool_calls' => [
                ['name' => 'search', 'arguments' => 'OpenAI'],
                ['name' => 'calculator', 'arguments' => '2+2']
            ]
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals('assistant', $message->role);
        $this->assertEquals('Here is the result', $message->content);
        $this->assertCount(2, $message->toolCalls);
        $this->assertEquals('search', $message->toolCalls[0]['name']);
        $this->assertEquals('OpenAI', $message->toolCalls[0]['arguments']);
        $this->assertEquals('calculator', $message->toolCalls[1]['name']);
        $this->assertEquals('2+2', $message->toolCalls[1]['arguments']);
    }

    public function testAddAttributesFromArray()
    {
        $array = [
            'role' => 'user',
            'content' => 'Hello world',
            'custom1' => 'value1',
            'custom2' => 'value2'
        ];
        
        $message = Message::fromArray($array);
        
        $this->assertEquals('value1', $message->custom1);
        $this->assertEquals('value2', $message->custom2);
    }
}

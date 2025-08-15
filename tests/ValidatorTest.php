<?php

namespace Ollama\Tests;

use PHPUnit_Framework_TestCase;
use Ollama\Utils\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidModelNames()
    {
        $validNames = array(
            'llama3.2',
            'orca-mini:3b-q8_0',
            'example/model:latest',
            'namespace/model',
            'model:tag'
        );

        foreach ($validNames as $name) {
            $this->assertTrue(Validator::isValidModelName($name), "Model name '$name' should be valid");
        }
    }

    public function testInvalidModelNames()
    {
        $invalidNames = array(
            '',
            'model with spaces',
            'model/with/too/many/slashes',
            '/invalid/start',
            'invalid:',
            ':invalid'
        );

        foreach ($invalidNames as $name) {
            $this->assertFalse(Validator::isValidModelName($name), "Model name '$name' should be invalid");
        }
    }

    public function testValidMessages()
    {
        $validMessage = array(
            'role' => 'user',
            'content' => 'Hello world'
        );

        $this->assertTrue(Validator::isValidMessage($validMessage));

        $validMessages = array(
            array('role' => 'system', 'content' => 'You are helpful'),
            array('role' => 'user', 'content' => 'Hello'),
            array('role' => 'assistant', 'content' => 'Hi there')
        );

        $this->assertTrue(Validator::isValidMessages($validMessages));
    }

    public function testInvalidMessages()
    {
        $invalidMessage = array(
            'role' => 'invalid_role',
            'content' => 'Hello'
        );

        $this->assertFalse(Validator::isValidMessage($invalidMessage));

        $messageWithoutRole = array(
            'content' => 'Hello'
        );

        $this->assertFalse(Validator::isValidMessage($messageWithoutRole));

        $emptyMessages = array();
        $this->assertFalse(Validator::isValidMessages($emptyMessages));
    }

    public function testValidSha256()
    {
        $validDigest = 'sha256:' . str_repeat('a', 64);
        $this->assertTrue(Validator::isValidSha256($validDigest));

        $invalidDigests = array(
            'invalid',
            'sha256:short',
            'sha256:' . str_repeat('g', 64), // invalid hex character
            'md5:' . str_repeat('a', 32)
        );

        foreach ($invalidDigests as $digest) {
            $this->assertFalse(Validator::isValidSha256($digest));
        }
    }

    public function testValidTool()
    {
        $validTool = array(
            'type' => 'function',
            'function' => array(
                'name' => 'get_weather',
                'description' => 'Get weather information'
            )
        );

        $this->assertTrue(Validator::isValidTool($validTool));

        $invalidTool = array(
            'type' => 'invalid_type',
            'function' => array()
        );

        $this->assertFalse(Validator::isValidTool($invalidTool));
    }
}

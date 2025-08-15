<?php

namespace Ollama\Tests;

use PHPUnit_Framework_TestCase;
use Ollama\Models\Model;

class ModelTest extends PHPUnit_Framework_TestCase
{
    public function testModelCreation()
    {
        $model = new Model('llama3.2');
        
        $this->assertEquals('llama3.2', $model->getName());
        $this->assertEquals(array(), $model->getParameters());
        $this->assertEquals(array(), $model->getOptions());
    }

    public function testModelWithParameters()
    {
        $parameters = array(
            'temperature' => 0.8,
            'top_p' => 0.9
        );
        
        $model = new Model('llama3.2', $parameters);
        
        $this->assertEquals($parameters, $model->getParameters());
    }

    public function testSettersAndGetters()
    {
        $model = new Model('llama3.2');
        
        $model->setTemperature(0.7);
        $this->assertEquals(0.7, $model->getTemperature());
        
        $model->setTopP(0.8);
        $this->assertEquals(0.8, $model->getTopP());
        
        $model->setSeed(42);
        $this->assertEquals(42, $model->getSeed());
        
        $model->setNumCtx(4096);
        $this->assertEquals(4096, $model->getNumCtx());
    }

    public function testFluentInterface()
    {
        $model = new Model('llama3.2');
        
        $result = $model->setTemperature(0.8)
                       ->setTopP(0.9)
                       ->setSeed(42);
        
        $this->assertSame($model, $result);
        $this->assertEquals(0.8, $model->getTemperature());
        $this->assertEquals(0.9, $model->getTopP());
        $this->assertEquals(42, $model->getSeed());
    }

    public function testToArray()
    {
        $model = new Model('llama3.2');
        $model->setTemperature(0.8)
              ->setTopP(0.9)
              ->setSeed(42)
              ->setOption('extra', 'value');
        
        $array = $model->toArray();
        
        $this->assertEquals('llama3.2', $array['model']);
        $this->assertEquals(0.8, $array['options']['temperature']);
        $this->assertEquals(0.9, $array['options']['top_p']);
        $this->assertEquals(42, $array['options']['seed']);
        $this->assertEquals('value', $array['extra']);
    }

    public function testCustomModel()
    {
        $custom = new Model('my-model', array('temperature' => 0.5), array('option' => 'test'));
        
        $this->assertEquals('my-model', $custom->getName());
        $this->assertEquals(0.5, $custom->getTemperature());
        $this->assertEquals('test', $custom->getOption('option'));
    }

    public function testClone()
    {
        $original = (new Model('qwen3:4b'))->setTemperature(0.8);
        $cloned = clone $original;
        
        $this->assertNotSame($original, $cloned);
        $this->assertEquals($original->getName(), $cloned->getName());
        $this->assertEquals($original->getTemperature(), $cloned->getTemperature());
    }

    public function testStopWords()
    {
        $model = new Model('llama3.2');
        $stopWords = array('stop1', 'stop2');
        
        $model->setStop($stopWords);
        
        $this->assertEquals($stopWords, $model->getStop());
    }

    public function testParameterRemoval()
    {
        $model = new Model('llama3.2');
        $model->setTemperature(0.8);
        
        $this->assertEquals(0.8, $model->getTemperature());
        
        $model->removeParameter('temperature');
        
        $this->assertNull($model->getTemperature());
    }

    public function testOptionsHandling()
    {
        $model = new Model('llama3.2');
        
        $options = array(
            'stream' => true,
            'format' => 'json'
        );
        
        $model->setOptions($options);
        
        $this->assertEquals($options, $model->getOptions());
        $this->assertTrue($model->getOption('stream'));
        $this->assertEquals('json', $model->getOption('format'));
        $this->assertNull($model->getOption('nonexistent'));
        $this->assertEquals('default', $model->getOption('nonexistent', 'default'));
    }

    public function testParametersWithOptions()
    {
        $model = new Model('llama3.2');
        $model->setTemperature(0.7)
              ->setOption('stream', true);
        
        $array = $model->toArray();
        
        $this->assertEquals('llama3.2', $array['model']);
        $this->assertEquals(0.7, $array['options']['temperature']);
        $this->assertTrue($array['stream']);
    }
}

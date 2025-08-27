<?php

use Vluzrmos\Ollama\Models\Model;

class ModelTest extends TestCase
{
    public function testModelConstruction()
    {
        $model = new Model($this->getTestModel());
        
        $this->assertEquals($this->getTestModel(), $model->getName());
        $this->assertEquals([], $model->getParameters());
        $this->assertEquals([], $model->getOptions());
    }

    public function testModelConstructionWithParameters()
    {
        $parameters = ['temperature' => 0.7, 'top_p' => 0.9];
        $options = ['stream' => true];
        
        $model = new Model($this->getTestModel(), $parameters, $options);
        
        $this->assertEquals($this->getTestModel(), $model->getName());
        $this->assertEquals($parameters, $model->getParameters());
        $this->assertEquals($options, $model->getOptions());
    }

    public function testSetAndGetName()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setName('llama3.2:3b');
        
        $this->assertEquals('llama3.2:3b', $model->getName());
        $this->assertSame($model, $result); // Test fluent interface
    }

    public function testSetAndGetParameters()
    {
        $model = new Model($this->getTestModel());
        $parameters = ['temperature' => 0.8, 'max_tokens' => 100];
        
        $result = $model->setParameters($parameters);
        
        $this->assertEquals($parameters, $model->getParameters());
        $this->assertSame($model, $result); // Test fluent interface
    }

    public function testSetAndGetParameter()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setParameter('temperature', 0.9);
        
        $this->assertEquals(0.9, $model->getParameter('temperature'));
        $this->assertSame($model, $result); // Test fluent interface
    }

    public function testGetParameterWithDefault()
    {
        $model = new Model($this->getTestModel());
        
        $this->assertNull($model->getParameter('nonexistent'));
        $this->assertEquals('default', $model->getParameter('nonexistent', 'default'));
    }

    public function testRemoveParameter()
    {
        $model = new Model($this->getTestModel());
        $model->setParameter('temperature', 0.7);
        
        $result = $model->removeParameter('temperature');
        
        $this->assertNull($model->getParameter('temperature'));
        $this->assertSame($model, $result); // Test fluent interface
    }

    public function testRemoveNonExistentParameter()
    {
        $model = new Model($this->getTestModel());
        
        // Should not throw error
        $result = $model->removeParameter('nonexistent');
        
        $this->assertSame($model, $result);
    }

    public function testTemperatureMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setTemperature(0.8);
        
        $this->assertEquals(0.8, $model->getTemperature());
        $this->assertSame($model, $result);
    }

    public function testTopPMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setTopP(0.95);
        
        $this->assertEquals(0.95, $model->getTopP());
        $this->assertSame($model, $result);
    }

    public function testTopKMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setTopK(40);
        
        $this->assertEquals(40, $model->getTopK());
        $this->assertSame($model, $result);
    }

    public function testRepeatPenaltyMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setRepeatPenalty(1.1);
        
        $this->assertEquals(1.1, $model->getRepeatPenalty());
        $this->assertSame($model, $result);
    }

    public function testSeedMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setSeed(12345);
        
        $this->assertEquals(12345, $model->getSeed());
        $this->assertSame($model, $result);
    }

    public function testNumCtxMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setNumCtx(2048);
        
        $this->assertEquals(2048, $model->getNumCtx());
        $this->assertSame($model, $result);
    }

    public function testNumPredictMethods()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setNumPredict(100);
        
        $this->assertEquals(100, $model->getNumPredict());
        $this->assertSame($model, $result);
    }

    public function testStopMethods()
    {
        $model = new Model($this->getTestModel());
        $stopWords = ['stop', 'end'];
        
        $result = $model->setStop($stopWords);
        
        $this->assertEquals($stopWords, $model->getStop());
        $this->assertSame($model, $result);
    }

    public function testSetAndGetOptions()
    {
        $model = new Model($this->getTestModel());
        $options = ['stream' => true, 'verbose' => false];
        
        $result = $model->setOptions($options);
        
        $this->assertEquals($options, $model->getOptions());
        $this->assertSame($model, $result);
    }

    public function testSetAndGetOption()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model->setOption('stream', true);
        
        $this->assertTrue($model->getOption('stream'));
        $this->assertSame($model, $result);
    }

    public function testGetOptionWithDefault()
    {
        $model = new Model($this->getTestModel());
        
        $this->assertNull($model->getOption('nonexistent'));
        $this->assertEquals('default', $model->getOption('nonexistent', 'default'));
    }

    public function testToArray()
    {
        $model = new Model($this->getTestModel());
        $model->setTemperature(0.7);
        $model->setOption('stream', true);
        $model->setOption('verbose', false);
        
        $array = $model->toArray();
        
        $this->assertEquals($this->getTestModel(), $array['model']);
        $this->assertEquals(['temperature' => 0.7], $array['options']);
        $this->assertTrue($array['stream']);
        $this->assertFalse($array['verbose']);
    }

    public function testToArrayWithoutParameters()
    {
        $model = new Model($this->getTestModel());
        $model->setOption('stream', true);
        
        $array = $model->toArray();
        
        $this->assertEquals($this->getTestModel(), $array['model']);
        $this->assertTrue($array['stream']);
        $this->assertArrayNotHasKey('options', $array);
    }

    public function testToArrayWithoutOptions()
    {
        $model = new Model($this->getTestModel());
        $model->setTemperature(0.7);
        
        $array = $model->toArray();
        
        $this->assertEquals($this->getTestModel(), $array['model']);
        $this->assertEquals(['temperature' => 0.7], $array['options']);
    }

    public function testClone()
    {
        $original = new Model($this->getTestModel());
        $original->setTemperature(0.7);
        $original->setOption('stream', true);
        
        $clone = clone $original;
        
        $this->assertEquals($original->getName(), $clone->getName());
        $this->assertEquals($original->getParameters(), $clone->getParameters());
        $this->assertEquals($original->getOptions(), $clone->getOptions());
        
        // Test that they are separate instances
        $clone->setTemperature(0.9);
        $this->assertEquals(0.7, $original->getTemperature());
        $this->assertEquals(0.9, $clone->getTemperature());
    }

    public function testFluentInterface()
    {
        $model = new Model($this->getTestModel());
        
        $result = $model
            ->setTemperature(0.8)
            ->setTopP(0.9)
            ->setTopK(50)
            ->setRepeatPenalty(1.2)
            ->setSeed(54321)
            ->setNumCtx(4096)
            ->setNumPredict(200)
            ->setStop(['end', 'finish']);
        
        $this->assertSame($model, $result);
        $this->assertEquals(0.8, $model->getTemperature());
        $this->assertEquals(0.9, $model->getTopP());
        $this->assertEquals(50, $model->getTopK());
        $this->assertEquals(1.2, $model->getRepeatPenalty());
        $this->assertEquals(54321, $model->getSeed());
        $this->assertEquals(4096, $model->getNumCtx());
        $this->assertEquals(200, $model->getNumPredict());
        $this->assertEquals(['end', 'finish'], $model->getStop());
    }

    public function testModelCreation()
    {
        $model = new Model($this->getTestModel());
        
        $this->assertEquals($this->getTestModel(), $model->getName());
        $this->assertEmpty($model->getParameters());
        $this->assertEmpty($model->getOptions());
    }

    public function testModelCreationWithParameters()
    {
        $parameters = array(
            'temperature' => 0.7,
            'top_p' => 0.9
        );
        
        $options = array(
            'max_tokens' => 100
        );
        
        $model = new Model($this->getTestModel(), $parameters, $options);
        
        $this->assertEquals($this->getTestModel(), $model->getName());
        $this->assertEquals($parameters, $model->getParameters());
        $this->assertEquals($options, $model->getOptions());
    }
}

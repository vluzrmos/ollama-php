<?php

require_once __DIR__ . '/../TestCase.php';

use Vluzrmos\Ollama\Models\Model;

class ModelTest extends TestCase
{
    public function testModelCreation()
    {
        $model = new Model('llama3.2:1b');
        
        $this->assertEquals('llama3.2:1b', $model->getName());
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
        
        $model = new Model('llama3.2:1b', $parameters, $options);
        
        $this->assertEquals('llama3.2:1b', $model->getName());
        $this->assertEquals($parameters, $model->getParameters());
        $this->assertEquals($options, $model->getOptions());
    }

    public function testSetAndGetName()
    {
        $model = new Model('llama3.2:1b');
        $model->setName('llama3.2:3b');
        
        $this->assertEquals('llama3.2:3b', $model->getName());
    }

    public function testSetAndGetParameters()
    {
        $model = new Model('llama3.2:1b');
        $parameters = array('temperature' => 0.8, 'top_k' => 50);
        
        $model->setParameters($parameters);
        
        $this->assertEquals($parameters, $model->getParameters());
    }

    public function testSetAndGetParameter()
    {
        $model = new Model('llama3.2:1b');
        
        $model->setParameter('temperature', 0.7);
        $this->assertEquals(0.7, $model->getParameter('temperature'));
        
        // Teste com valor padrão
        $this->assertNull($model->getParameter('nonexistent'));
        $this->assertEquals('default', $model->getParameter('nonexistent', 'default'));
    }

    public function testRemoveParameter()
    {
        $model = new Model('llama3.2:1b');
        $model->setParameter('temperature', 0.7);
        
        $this->assertEquals(0.7, $model->getParameter('temperature'));
        
        $model->removeParameter('temperature');
        $this->assertNull($model->getParameter('temperature'));
        
        // Remover parâmetro inexistente não deve causar erro
        $model->removeParameter('nonexistent');
    }

    public function testTemperatureMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setTemperature(0.9);
        $this->assertSame($model, $result); // Verifica fluent interface
        $this->assertEquals(0.9, $model->getTemperature());
    }

    public function testTopPMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setTopP(0.8);
        $this->assertSame($model, $result);
        $this->assertEquals(0.8, $model->getTopP());
    }

    public function testTopKMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setTopK(40);
        $this->assertSame($model, $result);
        $this->assertEquals(40, $model->getTopK());
    }

    public function testRepeatPenaltyMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setRepeatPenalty(1.1);
        $this->assertSame($model, $result);
        $this->assertEquals(1.1, $model->getRepeatPenalty());
    }

    public function testSeedMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setSeed(12345);
        $this->assertSame($model, $result);
        $this->assertEquals(12345, $model->getSeed());
    }

    public function testNumCtxMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setNumCtx(2048);
        $this->assertSame($model, $result);
        $this->assertEquals(2048, $model->getNumCtx());
    }

    public function testNumPredictMethods()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model->setNumPredict(100);
        $this->assertSame($model, $result);
        $this->assertEquals(100, $model->getNumPredict());
    }

    public function testStopMethods()
    {
        $model = new Model('llama3.2:1b');
        $stopWords = array('stop', 'end');
        
        $result = $model->setStop($stopWords);
        $this->assertSame($model, $result);
        $this->assertEquals($stopWords, $model->getStop());
    }

    public function testSetAndGetOptions()
    {
        $model = new Model('llama3.2:1b');
        $options = array('max_tokens' => 100, 'stream' => true);
        
        $model->setOptions($options);
        $this->assertEquals($options, $model->getOptions());
    }

    public function testSetAndGetOption()
    {
        $model = new Model('llama3.2:1b');
        
        $model->setOption('max_tokens', 150);
        $this->assertEquals(150, $model->getOption('max_tokens'));
        
        // Teste com valor padrão
        $this->assertNull($model->getOption('nonexistent'));
        $this->assertEquals('default', $model->getOption('nonexistent', 'default'));
    }

    public function testToArray()
    {
        $model = new Model('llama3.2:1b');
        $model->setTemperature(0.7);
        $model->setTopP(0.9);
        $model->setOption('max_tokens', 100);
        
        $array = $model->toArray();
        
        $expectedKeys = array('model', 'options', 'max_tokens');
        $this->assertArrayHasKeys($expectedKeys, $array);
        
        $this->assertEquals('llama3.2:1b', $array['model']);
        $this->assertEquals(100, $array['max_tokens']);
        
        // Verifica se os parâmetros estão em options
        $this->assertArrayHasKey('temperature', $array['options']);
        $this->assertArrayHasKey('top_p', $array['options']);
        $this->assertEquals(0.7, $array['options']['temperature']);
        $this->assertEquals(0.9, $array['options']['top_p']);
    }

    public function testToArrayWithoutParameters()
    {
        $model = new Model('llama3.2:1b');
        $array = $model->toArray();
        
        $this->assertEquals(array('model' => 'llama3.2:1b'), $array);
    }

    public function testFluentInterface()
    {
        $model = new Model('llama3.2:1b');
        
        $result = $model
            ->setTemperature(0.7)
            ->setTopP(0.9)
            ->setTopK(40)
            ->setRepeatPenalty(1.1)
            ->setSeed(12345);
        
        $this->assertSame($model, $result);
        $this->assertEquals(0.7, $model->getTemperature());
        $this->assertEquals(0.9, $model->getTopP());
        $this->assertEquals(40, $model->getTopK());
        $this->assertEquals(1.1, $model->getRepeatPenalty());
        $this->assertEquals(12345, $model->getSeed());
    }

    public function testClone()
    {
        $original = new Model('llama3.2:1b');
        $original->setTemperature(0.7);
        $original->setOption('max_tokens', 100);
        
        $cloned = clone $original;
        
        $this->assertEquals($original->getName(), $cloned->getName());
        $this->assertEquals($original->getParameters(), $cloned->getParameters());
        $this->assertEquals($original->getOptions(), $cloned->getOptions());
        
        // Verificar se são instâncias diferentes
        $this->assertNotSame($original, $cloned);
        
        // Modificar o clone não deve afetar o original
        $cloned->setTemperature(0.9);
        $this->assertEquals(0.7, $original->getTemperature());
        $this->assertEquals(0.9, $cloned->getTemperature());
    }
}

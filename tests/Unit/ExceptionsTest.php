<?php

use Vluzrmos\Ollama\Exceptions\OllamaException;
use Vluzrmos\Ollama\Exceptions\HttpException;
use Vluzrmos\Ollama\Exceptions\RequiredParameterException;
use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

class ExceptionsTest extends TestCase
{
    public function testOllamaExceptionCreation()
    {
        $exception = new OllamaException('Erro de teste', 123);
        
        $this->assertEquals('Erro de teste', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
        $this->assertNull($exception->getResponseData());
    }

    public function testOllamaExceptionWithResponseData()
    {
        $responseData = array(
            'error' => 'Modelo não encontrado',
            'code' => 'MODEL_NOT_FOUND'
        );
        
        $exception = new OllamaException('Erro de teste', 404, null, $responseData);
        
        $this->assertEquals('Erro de teste', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals($responseData, $exception->getResponseData());
    }

    public function testOllamaExceptionWithPreviousException()
    {
        $previous = new Exception('Erro anterior');
        $exception = new OllamaException('Erro de teste', 500, $previous);
        
        $this->assertEquals('Erro de teste', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testHttpExceptionCreation()
    {
        $exception = new HttpException('Erro HTTP', 400);
        
        $this->assertEquals('Erro HTTP', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\OllamaException', $exception);
    }

    public function testHttpExceptionWithResponseData()
    {
        $responseData = array(
            'detail' => 'Bad Request',
            'status' => 400
        );
        
        $exception = new HttpException('Requisição inválida', 400, null, $responseData);
        
        $this->assertEquals('Requisição inválida', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals($responseData, $exception->getResponseData());
    }


    public function testExceptionInheritance()
    {
        $ollamaException = new OllamaException('Teste');
        $httpException = new HttpException('Teste HTTP');
        
        // Verifica hierarquia de herança
        $this->assertInstanceOf('Exception', $ollamaException);
        $this->assertInstanceOf('Exception', $httpException);
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\OllamaException', $httpException);
    }

    public function testExceptionCanBeCaught()
    {
        $caught = false;
        
        try {
            throw new OllamaException('Teste');
        } catch (OllamaException $e) {
            $caught = true;
            $this->assertEquals('Teste', $e->getMessage());
        }
        
        $this->assertTrue($caught, 'Exceção não foi capturada');
    }

    public function testHttpExceptionCanBeCaughtAsOllamaException()
    {
        $caught = false;
        
        try {
            throw new HttpException('Erro HTTP', 500);
        } catch (OllamaException $e) {
            $caught = true;
            $this->assertEquals('Erro HTTP', $e->getMessage());
            $this->assertEquals(500, $e->getCode());
        }
        
        $this->assertTrue($caught, 'Exceção HTTP não foi capturada como OllamaException');
    }

    public function testOllamaExceptionConstruction()
    {
        $message = 'Test error message';
        $code = 500;
        
        $exception = new OllamaException($message, $code);
        
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\OllamaException', $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testHttpExceptionConstruction()
    {
        $message = 'HTTP error';
        $code = 404;
        $previous = new Exception('Previous error');
        $responseData = ['error' => 'Not found'];
        
        $exception = new HttpException($message, $code, $previous, $responseData);
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\OllamaException', $exception);
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\HttpException', $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals($responseData, $exception->getResponseData());
    }

    public function testHttpExceptionWithoutResponseData()
    {
        $exception = new HttpException('HTTP error', 500);
        
        $this->assertNull($exception->getResponseData());
    }

    public function testRequiredParameterExceptionParameterMethod()
    {
        $paramName = 'model';
        
        $exception = RequiredParameterException::parameter($paramName);
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\RequiredParameterException', $exception);
        $this->assertContains($paramName, $exception->getMessage());
        $this->assertContains('is required', $exception->getMessage());
    }

    public function testRequiredParameterExceptionConstruction()
    {
        $message = 'Custom required parameter message';
        
        $exception = new RequiredParameterException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testToolExecutionExceptionConstruction()
    {
        $message = 'Tool execution failed';
        $code = 100;
        
        $exception = new ToolExecutionException($message, $code);
        
        $this->assertInstanceOf('Vluzrmos\\Ollama\\Exceptions\\ToolExecutionException', $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testHttpExceptionGetResponseDataMethod()
    {
        $responseData = [
            'error' => 'Not found',
            'details' => 'The requested resource was not found'
        ];
        
        $exception = new HttpException('HTTP 404', 404, null, $responseData);
        
        $this->assertEquals($responseData, $exception->getResponseData());
        $this->assertEquals('Not found', $exception->getResponseData()['error']);
    }

    public function testRequiredParameterExceptionDifferentParameters()
    {
        $params = ['model', 'messages', 'prompt', 'input'];
        
        foreach ($params as $param) {
            $exception = RequiredParameterException::parameter($param);
            
            $this->assertContains($param, $exception->getMessage());
            $this->assertContains('is required', $exception->getMessage());
            $this->assertEquals(sprintf('parameter "%s" is required', $param), $exception->getMessage());
        }
    }
}

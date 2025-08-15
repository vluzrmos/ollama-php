<?php

require_once __DIR__ . '/../TestCase.php';

use Ollama\Exceptions\OllamaException;
use Ollama\Exceptions\HttpException;
use Ollama\Exceptions\ValidationException;

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
        $this->assertInstanceOf('Ollama\\Exceptions\\OllamaException', $exception);
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

    public function testValidationExceptionCreation()
    {
        $exception = new ValidationException('Parâmetro inválido');
        
        $this->assertEquals('Parâmetro inválido', $exception->getMessage());
        $this->assertInstanceOf('Ollama\\Exceptions\\OllamaException', $exception);
    }

    public function testExceptionInheritance()
    {
        $ollamaException = new OllamaException('Teste');
        $httpException = new HttpException('Teste HTTP');
        $validationException = new ValidationException('Teste validação');
        
        // Verifica hierarquia de herança
        $this->assertInstanceOf('Exception', $ollamaException);
        $this->assertInstanceOf('Exception', $httpException);
        $this->assertInstanceOf('Exception', $validationException);
        
        $this->assertInstanceOf('Ollama\\Exceptions\\OllamaException', $httpException);
        $this->assertInstanceOf('Ollama\\Exceptions\\OllamaException', $validationException);
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

    public function testValidationExceptionCanBeCaughtAsOllamaException()
    {
        $caught = false;
        
        try {
            throw new ValidationException('Erro de validação');
        } catch (OllamaException $e) {
            $caught = true;
            $this->assertEquals('Erro de validação', $e->getMessage());
        }
        
        $this->assertTrue($caught, 'Exceção de validação não foi capturada como OllamaException');
    }
}

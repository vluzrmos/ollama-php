<?php

namespace Ollama\Tools;

use Exception;

/**
 * Tool para operações matemáticas básicas
 * Exemplo de implementação de uma tool simples
 */
class CalculatorTool extends AbstractTool
{
    /**
     * Obtém o nome da tool
     *
     * @return string
     */
    public function getName()
    {
        return 'calculator';
    }

    /**
     * Obtém a descrição da tool
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Realizar operações matemáticas básicas (adição, subtração, multiplicação, divisão)';
    }

    /**
     * Obtém o schema dos parâmetros da tool
     *
     * @return array
     */
    public function getParametersSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'operation' => array(
                    'type' => 'string',
                    'description' => 'A operação matemática a ser realizada',
                    'enum' => array('add', 'subtract', 'multiply', 'divide', 'sqrt')
                ),
                'a' => array(
                    'type' => 'number',
                    'description' => 'O primeiro número'
                ),
                'b' => array(
                    'type' => 'number',
                    'description' => 'O segundo número'
                )
            ),
            'required' => array('operation', 'a', 'b')
        );
    }

    /**
     * Executa a tool com os parâmetros fornecidos
     *
     * @param array $arguments Argumentos passados pelo modelo
     * @return string Resultado da execução
     */
    public function execute(array $arguments)
    {
        $operation = isset($arguments['operation']) ? $arguments['operation'] : '';
        $a = isset($arguments['a']) ? (float) $arguments['a'] : 0;
        $b = isset($arguments['b']) ? (float) $arguments['b'] : 0;

        if (empty($operation)) {
            return json_encode(array(
                'error' => 'Operação é obrigatória'
            ));
        }

        try {
            $result = $this->calculate($operation, $a, $b);
            return json_encode(array(
                'operation' => $operation,
                'a' => $a,
                'b' => $b,
                'result' => $result
            ));
        } catch (Exception $e) {
            return json_encode(array(
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Realiza a operação matemática
     *
     * @param string $operation
     * @param float $a
     * @param float $b
     * @return float
     * @throws Exception
     */
    private function calculate($operation, $a, $b)
    {
        switch ($operation) {
            case 'add':
                return $a + $b;
            case 'subtract':
                return $a - $b;
            case 'multiply':
                return $a * $b;
            case 'divide':
                if ($b == 0) {
                    throw new Exception('Divisão por zero não é permitida');
                }
                return $a / $b;
            case 'sqrt':
                if ($a < 0) {
                    throw new Exception('Raiz quadrada de número negativo não é permitida');
                }
                return sqrt($a);
            default:
                throw new Exception('Operação não suportada: ' . $operation);
        }
    }
}

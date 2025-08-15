<?php

namespace Examples\Tools;

use Vluzrmos\Ollama\Tools\AbstractTool;

/**
 * Calculator tool to demonstrate tool system usage
 */
class CalculatorTool extends AbstractTool
{
    /**
     * Gets the tool name
     *
     * @return string
     */
    public function getName()
    {
        return 'calculator';
    }

    /**
     * Gets the tool description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Performs basic mathematical operations (addition, subtraction, multiplication, division)';
    }

    /**
     * Gets the tool parameter schema
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
                    'description' => 'Mathematical operation to perform',
                    'enum' => array('add', 'subtract', 'multiply', 'divide')
                ),
                'a' => array(
                    'type' => 'number',
                    'description' => 'First number'
                ),
                'b' => array(
                    'type' => 'number',
                    'description' => 'Second number'
                )
            ),
            'required' => array('operation', 'a', 'b')
        );
    }

    /**
     * Executes the tool with provided parameters
     *
     * @param array $arguments Arguments passed by the model
     * @return string Execution result
     */
    public function execute(array $arguments)
    {
        // Basic validation
        if (!isset($arguments['operation']) || !isset($arguments['a']) || !isset($arguments['b'])) {
            return json_encode(array(
                'error' => 'Required parameters not provided: operation, a, b'
            ));
        }

        $operation = $arguments['operation'];
        $a = floatval($arguments['a']);
        $b = floatval($arguments['b']);

        $result = null;

        switch ($operation) {
            case 'add':
                $result = $a + $b;
                break;
            case 'subtract':
                $result = $a - $b;
                break;
            case 'multiply':
                $result = $a * $b;
                break;
            case 'divide':
                if ($b == 0) {
                    return json_encode(array(
                        'error' => 'Division by zero is not allowed'
                    ));
                }
                $result = $a / $b;
                break;
            default:
                return json_encode(array(
                    'error' => 'Unsupported operation: ' . $operation
                ));
        }

        return json_encode(array(
            'operation' => $operation,
            'a' => $a,
            'b' => $b,
            'result' => $result
        ));
    }
}

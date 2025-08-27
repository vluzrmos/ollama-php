<?php

namespace Examples\Tools;

use Vluzrmos\Ollama\Exceptions\ToolExecutionException;
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
        return [
            'type' => 'object',
            'properties' => [
                'operation' => [
                    'type' => 'string',
                    'description' => 'Mathematical operation to perform',
                    'enum' => ['add', 'subtract', 'multiply', 'divide', 'sqrt']
                ],
                'a' => [
                    'type' => 'number',
                    'description' => 'First number'
                ],
                'b' => [
                    'type' => 'number',
                    'description' => 'Second number'
                ]
            ],
            'required' => ['operation', 'a', 'b']
        ];
    }

    /**
     * Executes the tool with provided parameters
     *
     * @param array $arguments Arguments passed by the model
     * @return string Execution result
     */
    public function execute(array $arguments)
    {
        echo basename(__FILE__, ".php") . ": Executing with arguments: " . json_encode($arguments) . PHP_EOL;

        if (isset($arguments['a']) && !isset($arguments['b'])) {
            $arguments['b'] = 0; // Default b to 0 if not provided
        }

        // Basic validation
        if (!isset($arguments['operation']) || !isset($arguments['a']) || !isset($arguments['b'])) {
            throw new ToolExecutionException("Missing required parameters: operation, a, b");
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
                    return new ToolExecutionException("Division by zero is not allowed");
                }

                $result = $a / $b;
                break;
            case 'sqrt': 
                if ($a < 0) {
                    return new ToolExecutionException("Cannot compute square root of a negative number");
                }

                $result = sqrt($a);
                break;
            default:
                return new ToolExecutionException("Unsupported operation: " . $operation);
        }

                $response = [
            'operation' => $operation,
            'a' => $a,
            'b' => $b,
            'result' => $result,
        ];

        echo basename(__FILE__, ".php") . ": Result: " . json_encode($response) . PHP_EOL;

        return $response;
    }
}

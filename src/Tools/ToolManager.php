<?php

namespace Vluzrmos\Ollama\Tools;

use JsonSerializable;
use Vluzrmos\Ollama\Exceptions\ToolExecutionException;
use Vluzrmos\Ollama\Models\Message;

/**
 * System tools manager
 * Responsible for registering, listing and executing available tools
 */
class ToolManager implements JsonSerializable
{
    /**
     * @var array<string,ToolInterface>
     */
    private $tools;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tools = [];
    }

    /**
     * Registers a new tool
     *
     * @param ToolInterface $tool
     * @return void
     */
    public function registerTool(ToolInterface $tool)
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * Removes a registered tool
     *
     * @param string $toolName
     * @return bool
     */
    public function unregisterTool($toolName)
    {
        if (isset($this->tools[$toolName])) {
            unset($this->tools[$toolName]);
            return true;
        }
        return false;
    }

    /**
     * Gets a tool by name
     *
     * @param string $toolName
     * @return ToolInterface|null
     */
    public function getTool($toolName)
    {
        return isset($this->tools[$toolName]) ? $this->tools[$toolName] : null;
    }

    /**
     * Lists all registered tools
     *
     * @return array
     */
    public function listTools()
    {
        return array_keys($this->tools);
    }

    public function jsonSerialize()
    {
        $tools = [];

        foreach ($this->tools as $tool) {
            $tools[] = $tool->jsonSerialize();
        }

        return $tools;
    }
    /**
     * Gets all tools in API format
     *
     * @return array
     */
    public function toArray()
    {
        $tools = [];

        foreach ($this->tools as $tool) {
            $tools[] = $tool->toArray();
        }

        return $tools;
    }

    /**
     * Executes a tool by name with arguments
     *
     * @param string $toolName
     * @param array $arguments
     * @return string
     * @throws Exception
     */
    public function executeTool($toolName, array $arguments)
    {
        $tool = $this->getTool($toolName);

        if ($tool === null) {
            return null;
        }

        return $tool->execute($arguments);
    }

    /**
     * Checks if a tool exists
     *
     * @param string $toolName
     * @return bool
     */
    public function hasTool($toolName)
    {
        return isset($this->tools[$toolName]);
    }

    /**
     * Executes multiple tool calls received from an API response
     * 
     * @param array $toolCalls Array of tool calls in OpenAI/Ollama format
     * @return array<mixed,ToolCallResult> Array with execution results
     * @throws Exception If any tool is not found
     */
    public function executeToolCalls(array $toolCalls)
    {
        $results = [];

        foreach ($toolCalls as $toolCall) {
            // Validate tool call structure
            if (!isset($toolCall['function'])) {
                $results[] = new ToolCallResult(
                    'invalid_function_name_'.uniqid(),
                    null,
                    false,
                    'Invalid tool call, function name not specified',
                    isset($toolCall['id']) ? $toolCall['id'] : null
                );

                continue;
            }

            $function = $toolCall['function'];
            $toolName = $function['name'];
            $toolId = isset($toolCall['id']) ? $toolCall['id'] : null;

            try {
                $arguments = $this->decodeToolCallArguments(isset($function['arguments']) ? $function['arguments'] : null);
            } catch (\InvalidArgumentException $e) {
                $results[] = new ToolCallResult(
                    $toolName,
                    null,
                    false,
                    $e->getMessage(),
                    $toolId
                );

                continue;
            }

            // Execute the tool
            try {
                if (!$this->hasTool($toolName)) {
                    throw new ToolExecutionException("Tool \"{$toolName}\" wasn't found");
                }

                $result = $this->executeTool($toolName, $arguments);

                $results[] = new ToolCallResult(
                    $toolName,
                    $result,
                    true,
                    null,
                    $toolId
                );
            } catch (\Exception $e) {
                if ($e instanceof ToolExecutionException) {
                    $message = $e->getMessage();
                } else {
                    $message = "Error executing tool \"{$toolName}\"". ($toolId? " (id: {$toolId})" : "");
                }

                $results[] = new ToolCallResult(
                    $toolName,
                    null,
                    false,
                    $message,
                    $toolId
                );
            }
        }

        return $results;
    }

    public function decodeToolCallArguments($arguments) {
        if (empty($arguments)) {
            return [];
        }

        if (is_array($arguments)) {
            return $arguments;
        }

        $arguments = json_decode($arguments, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $arguments;
        }

        throw new \InvalidArgumentException('Invalid JSON arguments: ' . json_last_error_msg());            
    }

    /**
     * Converts tool call results to response message format
     * 
     * @param array<mixed,ToolCallResult> $toolCallResults Results from executeToolCalls method
     * @return array Array of messages in the expected API format
     */
    public function toolCallResultsToMessages(array $toolCallResults)
    {
        $messages = [];

        foreach ($toolCallResults as $result) {
            $messages[] = $result->toMessage();
        }

        return $messages;
    }

    /**
     * Gets statistics about registered tools
     *
     * @return array
     */
    public function getStats()
    {
        $stats = [
            'total_tools' => count($this->tools),
            'tools' => []
        ];

        foreach ($this->tools as $name => $tool) {
            $parametersSchema = $tool->getParametersSchema();
            $properties = isset($parametersSchema['properties']) ? $parametersSchema['properties'] : [];

            $stats['tools'][$name] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'parameters_count' => is_array($properties) ? count($properties) : 0,
            ];
        }

        return $stats;
    }
}

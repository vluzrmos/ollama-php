<?php

namespace Vluzrmos\Ollama\Tools;

use JsonSerializable;
use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

/**
 * System tools manager
 * Responsible for registering, listing and executing available tools
 */
class ToolManager implements JsonSerializable
{
    /**
     * @var array
     */
    private $tools;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->tools = array();
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
        $tools = array();

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
            return json_encode(array(
                'error' => 'Tool not found: ' . $toolName
            ));
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
     * @return array Array with execution results
     * @throws Exception If any tool is not found
     */
    public function executeToolCalls(array $toolCalls)
    {
        $results = array();

        foreach ($toolCalls as $toolCall) {
            // Validate tool call structure
            if (!isset($toolCall['function'])) {
                $results[] = array(
                    'id' => isset($toolCall['id']) ? $toolCall['id'] : null,
                    'error' => 'Invalid tool call, function name not specified',
                    'success' => false
                );

                continue;
            }

            $function = $toolCall['function'];
            $toolName = $function['name'];
            $toolId = isset($toolCall['id']) ? $toolCall['id'] : null;

            // Decode arguments (which may come as JSON string)
            $arguments = array();
            if (isset($function['arguments'])) {
                if (is_string($function['arguments'])) {
                    $decodedArgs = json_decode($function['arguments'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $arguments = $decodedArgs;
                    } else {
                        $results[] = array(
                            'id' => $toolId,
                            'error' => 'Invalid JSON arguments: ' . json_last_error_msg(),
                            'success' => false,
                            'tool_name' => $toolName
                        );
                        continue;
                    }
                } else if (is_array($function['arguments'])) {
                    $arguments = $function['arguments'];
                }
            }

            // Execute the tool
            try {
                if (!$this->hasTool($toolName)) {
                    throw new ToolExecutionException("Tool \"{$toolName}\" wasn't found");
                }

                $result = $this->executeTool($toolName, $arguments);

                $results[] = array(
                    'id' => $toolId,
                    'result' => $result,
                    'success' => true,
                    'tool_name' => $toolName
                );
            } catch (\Exception $e) {
                if ($e instanceof ToolExecutionException) {
                    $message = $e->getMessage();
                } else {
                    $message = "Error executing tool \"{$toolName}\"". ($toolId? " (id: {$toolId})" : "");
                }

                $results[] = array(
                    'id' => $toolId,
                    'error' => $message,
                    'success' => false,
                    'tool_name' => $toolName
                );
            }
        }

        return $results;
    }

    /**
     * Converts tool call results to response message format
     * 
     * @param array $toolCallResults Results from executeToolCalls method
     * @return array Array of messages in the expected API format
     */
    public function toolCallResultsToMessages(array $toolCallResults)
    {
        $messages = array();

        foreach ($toolCallResults as $result) {
            $content = '';

            if ($result['success']) {
                $content = is_string($result['result']) ? $result['result'] : json_encode($result['result']);
            } else {
                $content = 'Error: ' . $result['error'];
            }

            $message = array(
                'role' => 'tool',
                'content' => $content
            );

            // Add tool_call_id if available (OpenAI format)
            if (isset($result['id']) && $result['id'] !== null) {
                $message['tool_call_id'] = $result['id'];
            }

            // Add tool name if available (Ollama format)
            if (isset($result['tool_name'])) {
                $message['tool_name'] = $result['tool_name'];
            }

            $messages[] = $message;
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
        $stats = array(
            'total_tools' => count($this->tools),
            'tools' => array()
        );

        foreach ($this->tools as $name => $tool) {
            $stats['tools'][$name] = array(
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'parameters_count' => count($tool->getParametersSchema()['properties'])
            );
        }

        return $stats;
    }
}

<?php

namespace Vluzrmos\Ollama\Agents;

use Vluzrmos\Ollama\Tools\ToolInterface;
use Vluzrmos\Ollama\Tools\ToolManager;
use Vluzrmos\Ollama\Models\Message;

/**
 * Basic implementation of an AI agent that can process queries using a specific model and tools
 */
class Agent implements AgentInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $instructions;

    /**
     * @var string
     */
    private $model;

    /**
     * @var ClientAdapterInterface
     */
    private $clientAdapter;

    /**
     * @var ToolManager
     */
    private $toolManager;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $name Agent name/identifier
     * @param ClientAdapterInterface $clientAdapter Client adapter (OpenAI/Ollama)
     * @param string $model Model name to use
     * @param string $instructions System instructions for the agent
     * @param string $description Agent description
     * @param array|ToolManager $tools Array of ToolInterface instances or ToolManager instance
     * @param array $options Additional options for the agent
     */
    public function __construct(
        $name,
        ClientAdapterInterface $clientAdapter,
        $model,
        $instructions,
        $description = '',
        $tools = [],
        array $options = []
    ) {
        $this->name = $name;
        $this->clientAdapter = $clientAdapter;
        $this->model = $model;
        $this->instructions = $instructions;
        $this->description = $description ?: $name;
        $this->options = $options;

        // Initialize ToolManager
        if ($tools instanceof ToolManager) {
            $this->toolManager = $tools;
        } else {
            $this->toolManager = new ToolManager();
            // Register tools if array provided
            if (is_array($tools)) {
                foreach ($tools as $tool) {
                    if ($tool instanceof ToolInterface) {
                        $this->toolManager->registerTool($tool);
                    }
                }
            }
        }
    }

    /**
     * Gets the agent name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the agent description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gets the agent instructions/prompt
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Gets the tool manager with all tools available to this agent
     *
     * @return ToolManager
     */
    public function getTools()
    {
        return $this->toolManager;
    }

    /**
     * Gets the model name
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Gets the client adapter
     *
     * @return ClientAdapterInterface
     */
    public function getClientAdapter()
    {
        return $this->clientAdapter;
    }

    /**
     * Process a user query and return a response
     *
     * @param string $message User message/query
     * @param array $conversationHistory Previous messages in conversation
     * @param array $options Additional options for processing
     * @return mixed Response from the agent
     */
    public function processQuery($message, array $conversationHistory = [], array $options = [])
    {
        // Build the conversation messages
        $messages = [];
        
        // Add system instructions
        if (!empty($this->instructions)) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->instructions
            ];
        }

        // Add conversation history
        foreach ($conversationHistory as $historyMessage) {
            if ($historyMessage instanceof Message) {
                $messages[] = [
                    'role' => $historyMessage->role,
                    'content' => $historyMessage->content
                ];
            } elseif (is_array($historyMessage) && isset($historyMessage['role'], $historyMessage['content'])) {
                $messages[] = $historyMessage;
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        // Prepare tools in the correct format
        $toolsArray = $this->toolManager->toArray();

        // Merge options with agent's default options
        $requestOptions = array_merge($this->options, $options);

        // Send request to the client
        $response = $this->clientAdapter->chatCompletion(
            $this->model,
            $messages,
            $toolsArray,
            $requestOptions
        );

        // Process tool calls if present in response
        if ($this->hasToolCalls($response)) {
            return $this->processToolCalls($response, $messages, $toolsArray, $requestOptions);
        }

        return $response;
    }

    /**
     * Check if this agent can handle the given query
     * Default implementation always returns true
     * Override this in specialized agents for more specific logic
     *
     * @param string $message User message/query
     * @param array $conversationHistory Previous messages in conversation
     * @return bool True if agent can handle the query
     */
    public function canHandle($message, array $conversationHistory = [])
    {
        // Default implementation - agent can handle any query
        return true;
    }

    /**
     * Check if response contains tool calls
     *
     * @param mixed $response
     * @return bool
     */
    protected function hasToolCalls($response)
    {
        if (is_object($response) && method_exists($response, 'toArray')) {
            $responseArray = $response->toArray();
        } elseif (is_array($response)) {
            $responseArray = $response;
        } else {
            return false;
        }

        // Check for tool calls in the response structure
        return isset($responseArray['choices'][0]['message']['tool_calls']) ||
               (isset($responseArray['message']['tool_calls']) && !empty($responseArray['message']['tool_calls']));
    }

    /**
     * Process tool calls in the response
     *
     * @param mixed $response
     * @param array $messages
     * @param array $toolsArray
     * @param array $requestOptions
     * @return mixed
     */
    protected function processToolCalls($response, array $messages, array $toolsArray, array $requestOptions)
    {
        if (is_object($response) && method_exists($response, 'toArray')) {
            $responseArray = $response->toArray();
        } else {
            $responseArray = $response;
        }

        // Extract tool calls from response
        $toolCalls = [];
        if (isset($responseArray['choices'][0]['message']['tool_calls'])) {
            $toolCalls = $responseArray['choices'][0]['message']['tool_calls'];
        } elseif (isset($responseArray['message']['tool_calls'])) {
            $toolCalls = $responseArray['message']['tool_calls'];
        }

        // Add assistant message with tool calls to conversation
        $messages[] = [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => $toolCalls
        ];

        // Execute each tool call
        foreach ($toolCalls as $toolCall) {
            $toolName = $toolCall['function']['name'];
            $toolArgs = json_decode($toolCall['function']['arguments'], true);
            
            // Use ToolManager to execute the tool
            $toolResult = null;
            try {
                $toolResult = $this->toolManager->executeTool($toolName, $toolArgs);
            } catch (\Exception $e) {
                $toolResult = 'Error executing tool: ' . $e->getMessage();
            }

            // Add tool result to conversation
            $messages[] = [
                'role' => 'tool',
                'content' => is_string($toolResult) ? $toolResult : json_encode($toolResult),
                'tool_call_id' => $toolCall['id']
            ];
        }

        // Send follow-up request with tool results
        return $this->clientAdapter->chatCompletion(
            $this->model,
            $messages,
            $toolsArray,
            $requestOptions
        );
    }

    /**
     * Add a tool to the agent
     *
     * @param ToolInterface $tool
     * @return void
     */
    public function addTool(ToolInterface $tool)
    {
        $this->toolManager->registerTool($tool);
    }

    /**
     * Remove a tool from the agent
     *
     * @param string $toolName
     * @return bool True if tool was found and removed
     */
    public function removeTool($toolName)
    {
        return $this->toolManager->unregisterTool($toolName);
    }

    /**
     * Set agent options
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get agent options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
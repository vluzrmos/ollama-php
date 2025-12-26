<?php

namespace Vluzrmos\Ollama\Agents;

/**
 * Agent that groups multiple other agents and chooses the best one for each query
 */
class AgentGroup implements AgentInterface
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
     * @var AgentInterface[]
     */
    private $agents;

    /**
     * @var ClientAdapterInterface
     */
    private $clientAdapter;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $selectorInstructions;

    /**
     * Default instructions for agent selection
     */
    const DEFAULT_SELECTOR_INSTRUCTIONS = 'You are an intelligent agent coordinator. Your job is to analyze user queries and either:
1. Respond directly if the query is simple and general
2. Choose the most appropriate specialized agent from the available agents

Available agents:
{AGENT_LIST}

For each query, analyze:
- The subject matter and complexity
- Which agent would be most qualified to handle it
- Whether the query requires specialized knowledge or tools

If you choose an agent, respond with exactly: "AGENT: agent_name"
If you will respond directly, just provide your response without any prefixes.';

    /**
     * @param string $name Group name
     * @param ClientAdapterInterface $clientAdapter Client adapter for the selector
     * @param string $model Model to use for agent selection
     * @param array $agents Array of AgentInterface instances
     * @param string $description Group description
     * @param string $selectorInstructions Custom instructions for agent selection
     */
    public function __construct(
        $name,
        ClientAdapterInterface $clientAdapter,
        $model,
        array $agents = [],
        $description = '',
        $selectorInstructions = ''
    ) {
        $this->name = $name;
        $this->clientAdapter = $clientAdapter;
        $this->model = $model;
        $this->agents = $agents;
        $this->description = $description ?: 'Agent Group: ' . $name;
        $this->selectorInstructions = $selectorInstructions ?: self::DEFAULT_SELECTOR_INSTRUCTIONS;
    }

    /**
     * Gets the agent group name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the agent group description for direct responses
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gets the selector instructions for agent selection
     * it includes the list of available agents {AGENT_LIST} placeholder
     * and the "AGENT: agent_name" response format.
     *
     * @return string
     */
    public function getInstructions()
    {
        $agentList = $this->buildAgentList();
        return str_replace('{AGENT_LIST}', $agentList, $this->selectorInstructions);
    }

    /**
     * Gets all tools from all agents in the group
     *
     * @return \Vluzrmos\Ollama\Tools\ToolManager
     */
    public function getTools()
    {
        $allToolsManager = new \Vluzrmos\Ollama\Tools\ToolManager();
        
        foreach ($this->agents as $agent) {
            $agentToolManager = $agent->getTools();
            if ($agentToolManager instanceof \Vluzrmos\Ollama\Tools\ToolManager) {
                $toolNames = $agentToolManager->listTools();
                foreach ($toolNames as $toolName) {
                    $tool = $agentToolManager->getTool($toolName);
                    if ($tool && !$allToolsManager->hasTool($toolName)) {
                        $allToolsManager->registerTool($tool);
                    }
                }
            }
        }
        
        return $allToolsManager;
    }

    /**
     * Process a user query by selecting the best agent or responding directly
     *
     * @param string $message User message/query
     * @param array $conversationHistory Previous messages in conversation
     * @param array $options Additional options for processing
     * @return mixed Response from the selected agent or direct response
     */
    public function processQuery($message, array $conversationHistory = [], array $options = [])
    {
        // Use the selector to determine what to do
        $selectorResponse = $this->selectAgent($message, $conversationHistory, $options);
        
        // Check if a specific agent was selected
        if (preg_match('/^AGENT:\s*(.+)$/i', trim($selectorResponse), $matches)) {
            $selectedAgentName = trim($matches[1]);
            
            // Find and use the selected agent
            foreach ($this->agents as $agent) {
                if (strcasecmp($agent->getName(), $selectedAgentName) === 0) {
                    return $agent->processQuery($message, $conversationHistory, $options);
                }
            }
            
            // If agent not found, fall back to direct response
            return $this->processDirectQuery($message, $conversationHistory, $options);
        }
        
        // Direct response from selector
        return $selectorResponse;
    }

    /**
     * Check if this agent group can handle the given query
     * Always returns true since it can either handle directly or delegate
     *
     * @param string $message User message/query
     * @param array $conversationHistory Previous messages in conversation
     * @return bool Always true
     */
    public function canHandle($message, array $conversationHistory = [])
    {
        return true;
    }

    /**
     * Add an agent to the group
     *
     * @param AgentInterface $agent
     * @return void
     */
    public function addAgent(AgentInterface $agent)
    {
        $this->agents[] = $agent;
    }

    /**
     * Remove an agent from the group
     *
     * @param string $agentName
     * @return bool True if agent was found and removed
     */
    public function removeAgent($agentName)
    {
        foreach ($this->agents as $index => $agent) {
            if ($agent->getName() === $agentName) {
                unset($this->agents[$index]);
                $this->agents = array_values($this->agents); // Re-index array
                return true;
            }
        }
        return false;
    }

    /**
     * Get all agents in the group
     *
     * @return AgentInterface[]
     */
    public function getAgents()
    {
        return $this->agents;
    }

    /**
     * Get an agent by name
     *
     * @param string $name
     * @return AgentInterface|null
     */
    public function getAgent($name)
    {
        foreach ($this->agents as $agent) {
            if ($agent->getName() === $name) {
                return $agent;
            }
        }
        return null;
    }

    /**
     * Set custom selector instructions
     *
     * @param string $instructions
     * @return void
     */
    public function setSelectorInstructions($instructions)
    {
        $this->selectorInstructions = $instructions;
    }

    /**
     * Use the selector to determine which agent to use or respond directly
     *
     * @param string $message
     * @param array $conversationHistory
     * @param array $options
     * @return string
     */
    protected function selectAgent($message, array $conversationHistory = [], array $options = [])
    {
        $messages = [];
        
        // Add selector system instructions
        $messages[] = [
            'role' => 'system',
            'content' => $this->getInstructions()
        ];

        // Add conversation history for context
        foreach ($conversationHistory as $historyMessage) {
            if (is_array($historyMessage) && isset($historyMessage['role'], $historyMessage['content'])) {
                $messages[] = $historyMessage;
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        try {
            $response = $this->clientAdapter->chatCompletion($this->model, $messages, [], $options);
            
            // Extract text content from response
            if (is_object($response) && method_exists($response, 'toArray')) {
                $responseArray = $response->toArray();
                if (isset($responseArray['choices'][0]['message']['content'])) {
                    return $responseArray['choices'][0]['message']['content'];
                } elseif (isset($responseArray['message']['content'])) {
                    return $responseArray['message']['content'];
                }
            } elseif (is_array($response)) {
                if (isset($response['choices'][0]['message']['content'])) {
                    return $response['choices'][0]['message']['content'];
                } elseif (isset($response['message']['content'])) {
                    return $response['message']['content'];
                }
            }
            
            return 'Unable to process selector response.';
            
        } catch (\Exception $e) {
            // If selector fails, try to find an agent that can handle the query
            foreach ($this->agents as $agent) {
                if ($agent->canHandle($message, $conversationHistory)) {
                    return $agent->processQuery($message, $conversationHistory, $options);
                }
            }
            
            // Last resort: process as direct query
            return $this->processDirectQuery($message, $conversationHistory, $options);
        }
    }

    /**
     * Process query directly without delegating to any specific agent
     *
     * @param string $message
     * @param array $conversationHistory
     * @param array $options
     * @return mixed
     */
    protected function processDirectQuery($message, array $conversationHistory = [], array $options = [])
    {
        $messages = [];
        
        // Add basic instructions for direct response
        $messages[] = [
            'role' => 'system',
            'content' => $this->getDescription(),
        ];

        // Add conversation history
        foreach ($conversationHistory as $historyMessage) {
            if (is_array($historyMessage) && isset($historyMessage['role'], $historyMessage['content'])) {
                $messages[] = $historyMessage;
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        return $this->clientAdapter->chatCompletion($this->model, $messages, [], $options);
    }

    /**
     * Build a formatted list of available agents for the selector instructions
     *
     * @return string
     */
    protected function buildAgentList()
    {
        $agentList = [];
        foreach ($this->agents as $agent) {
            $toolManager = $agent->getTools();
            $toolNames = [];
            if ($toolManager instanceof \Vluzrmos\Ollama\Tools\ToolManager) {
                $toolNames = $toolManager->listTools();
            }
            
            $toolsText = !empty($toolNames) ? ' (Tools: ' . implode(', ', $toolNames) . ')' : '';
            $agentList[] = '- ' . $agent->getName() . ': ' . $agent->getDescription() . $toolsText;
        }
        
        return implode("\n", $agentList);
    }
}
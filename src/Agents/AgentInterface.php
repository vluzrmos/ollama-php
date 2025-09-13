<?php

namespace Vluzrmos\Ollama\Agents;

/**
 * Interface for all agents that can process user queries
 */
interface AgentInterface
{
    /**
     * Gets the agent name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the agent description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Gets the agent instructions/prompt
     *
     * @return string
     */
    public function getInstructions();

    /**
     * Gets the tool manager with all tools available to this agent
     *
     * @return \Vluzrmos\Ollama\Tools\ToolManager
     */
    public function getTools();

    /**
     * Process a user query and return a response
     *
     * @param string $message User message/query
     * @param array $conversationHistory Previous messages in conversation
     * @param array $options Additional options for processing
     * @return mixed Response from the agent
     */
    public function processQuery($message, array $conversationHistory = [], array $options = []);

    /**
     * Check if this agent can handle the given query
     * Used by AgentGroup to determine the best agent for a query
     *
     * @param string $message User message/query
     * @param array $conversationHistory Previous messages in conversation
     * @return bool True if agent can handle the query
     */
    public function canHandle($message, array $conversationHistory = []);
}
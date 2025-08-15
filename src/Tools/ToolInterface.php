<?php
namespace Vluzrmos\Ollama\Tools;

use JsonSerializable;

interface ToolInterface extends JsonSerializable
{
    /**
     * Gets the tool name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the tool description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Gets the tool parameter schema
     *
     * @return array
     */
    public function getParametersSchema();

    /**
     * Converts the tool to the expected API format
     *
     * @return array
     */
    public function toArray();

    /**
     * Executes the tool with provided parameters
     *
     * @param array $arguments Arguments passed by the model
     * @return mixed Execution result
     */
    public function execute(array $arguments);
}
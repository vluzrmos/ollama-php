<?php

namespace Vluzrmos\Ollama\Tools;

use JsonSerializable;

/**
 * Abstract base class for tool implementation
 * Provides a default implementation of the toArray() method
 */
abstract class AbstractTool implements ToolInterface
{
    /**
     * Converts the tool to the expected API format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'type' => 'function',
            'function' => array(
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => $this->getParametersSchema()
            )
        );
    }


    /**
     * Json serialize method for compatibility with JSON encoding
     * 
     * Enforce parameters and required fields to be objects
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = $this->toArray();

        if (isset($data['parameters'])) {
            $parameters = $data['parameters'];
            $required = isset($parameters['required']) ? $parameters['required'] : array();

            $parameters['required'] = (object) $required;
            $data['parameters'] = (object) $parameters;
        }

        return $data;
    }
}

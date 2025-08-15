<?php

namespace Ollama\Tools;

use JsonSerializable;

/**
 * Classe abstrata base para implementação de tools
 * Fornece uma implementação padrão do método toArray()
 */
abstract class AbstractTool implements ToolInterface
{
    /**
     * Converte a tool para o formato esperado pela API
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

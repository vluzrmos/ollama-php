<?php

namespace Ollama\Models;

/**
 * Representa uma tool (ferramenta) para function calling
 */
class Tool
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $function;

    /**
     * @param string $name Nome da função
     * @param string $description Descrição da função
     * @param array $parameters Parâmetros da função
     */
    public function __construct($name, $description, array $parameters = array())
    {
        $this->type = 'function';
        $this->function = array(
            'name' => $name,
            'description' => $description,
            'parameters' => $parameters
        );
    }

    /**
     * Cria uma tool para obter clima
     *
     * @return Tool
     */
    public static function weather()
    {
        return new self(
            'get_weather',
            'Obter informações do clima para uma cidade',
            array(
                'type' => 'object',
                'properties' => array(
                    'city' => array(
                        'type' => 'string',
                        'description' => 'A cidade para obter o clima'
                    ),
                    'format' => array(
                        'type' => 'string',
                        'description' => 'Formato da temperatura (celsius ou fahrenheit)',
                        'enum' => array('celsius', 'fahrenheit')
                    )
                ),
                'required' => array('city', 'format')
            )
        );
    }

    /**
     * Cria uma tool para busca web
     *
     * @return Tool
     */
    public static function webSearch()
    {
        return new self(
            'web_search',
            'Buscar informações na web',
            array(
                'type' => 'object',
                'properties' => array(
                    'query' => array(
                        'type' => 'string',
                        'description' => 'Consulta de busca'
                    ),
                    'max_results' => array(
                        'type' => 'integer',
                        'description' => 'Número máximo de resultados',
                        'default' => 5
                    )
                ),
                'required' => array('query')
            )
        );
    }

    /**
     * Cria uma tool personalizada
     *
     * @param string $name
     * @param string $description
     * @param array $properties
     * @param array $required
     * @return Tool
     */
    public static function create($name, $description, array $properties, array $required = array())
    {
        return new self($name, $description, array(
            'type' => 'object',
            'properties' => $properties,
            'required' => $required
        ));
    }

    /**
     * Converte a tool para array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'type' => $this->type,
            'function' => $this->function
        );
    }
}

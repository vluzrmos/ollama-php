<?php

namespace Ollama\Tools;

use JsonSerializable;

/**
 * Gerenciador de tools do sistema
 * Responsável por registrar, listar e executar tools disponíveis
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
     * Registra uma nova tool
     *
     * @param ToolInterface $tool
     * @return void
     */
    public function registerTool(ToolInterface $tool)
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * Remove uma tool registrada
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
     * Obtém uma tool pelo nome
     *
     * @param string $toolName
     * @return ToolInterface|null
     */
    public function getTool($toolName)
    {
        return isset($this->tools[$toolName]) ? $this->tools[$toolName] : null;
    }

    /**
     * Lista todas as tools registradas
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
     * Obtém todas as tools no formato da API
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
     * Executa uma tool pelo nome com argumentos
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
                'error' => 'Tool não encontrada: ' . $toolName
            ));
        }

        return $tool->execute($arguments);
    }

    /**
     * Verifica se uma tool existe
     *
     * @param string $toolName
     * @return bool
     */
    public function hasTool($toolName)
    {
        return isset($this->tools[$toolName]);
    }

    /**
     * Registra tools padrão do sistema
     *
     * @return void
     */
    public function registerDefaultTools()
    {
        $this->registerTool(new WeatherTool());
        $this->registerTool(new CalculatorTool());
        $this->registerTool(new WebSearchTool());
        $this->registerTool(new DateTimeTool());
    }

    /**
     * Obtém estatísticas sobre as tools registradas
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

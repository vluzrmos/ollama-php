<?php
namespace Vluzrmos\Ollama\Tools;

use JsonSerializable;

interface ToolInterface extends JsonSerializable
{
    /**
     * Obtém o nome da tool
     *
     * @return string
     */
    public function getName();

    /**
     * Obtém a descrição da tool
     *
     * @return string
     */
    public function getDescription();

    /**
     * Obtém o schema dos parâmetros da tool
     *
     * @return array
     */
    public function getParametersSchema();

    /**
     * Converte a tool para o formato esperado pela API
     *
     * @return array
     */
    public function toArray();

    /**
     * Executa a tool com os parâmetros fornecidos
     *
     * @param array $arguments Argumentos passados pelo modelo
     * @return mixed Resultado da execução
     */
    public function execute(array $arguments);
}
<?php

namespace Ollama\Tools;

use Exception;

/**
 * Tool para manipulação de datas
 * Exemplo de implementação de uma tool para operações com data/hora
 */
class DateTimeTool extends AbstractTool
{
    /**
     * Obtém o nome da tool
     *
     * @return string
     */
    public function getName()
    {
        return 'datetime_operations';
    }

    /**
     * Obtém a descrição da tool
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Realizar operações com data e hora como obter data atual, formatar datas, calcular diferenças';
    }

    /**
     * Obtém o schema dos parâmetros da tool
     *
     * @return array
     */
    public function getParametersSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'operation' => array(
                    'type' => 'string',
                    'description' => 'A operação a ser realizada',
                    'enum' => array('current', 'format', 'add_days', 'diff_days')
                ),
                'date' => array(
                    'type' => 'string',
                    'description' => 'Data no formato Y-m-d H:i:s ou Y-m-d (opcional para current)'
                ),
                'format' => array(
                    'type' => 'string',
                    'description' => 'Formato de saída da data (ex: d/m/Y, Y-m-d H:i:s)'
                ),
                'days' => array(
                    'type' => 'integer',
                    'description' => 'Número de dias para adicionar (pode ser negativo para subtrair)'
                ),
                'date2' => array(
                    'type' => 'string',
                    'description' => 'Segunda data para calcular diferença'
                )
            ),
            'required' => array('operation')
        );
    }

    /**
     * Executa a tool com os parâmetros fornecidos
     *
     * @param array $arguments Argumentos passados pelo modelo
     * @return string Resultado da execução
     */
    public function execute(array $arguments)
    {
        $operation = isset($arguments['operation']) ? $arguments['operation'] : '';

        if (empty($operation)) {
            return json_encode(array(
                'error' => 'Operação é obrigatória'
            ));
        }

        try {
            $result = $this->performDateOperation($operation, $arguments);
            return json_encode($result);
        } catch (Exception $e) {
            return json_encode(array(
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Realiza a operação de data solicitada
     *
     * @param string $operation
     * @param array $arguments
     * @return array
     * @throws Exception
     */
    private function performDateOperation($operation, $arguments)
    {
        switch ($operation) {
            case 'current':
                $format = isset($arguments['format']) ? $arguments['format'] : 'Y-m-d H:i:s';
                return array(
                    'operation' => 'current',
                    'result' => date($format),
                    'timezone' => date_default_timezone_get()
                );

            case 'format':
                if (!isset($arguments['date'])) {
                    throw new Exception('Data é obrigatória para formatação');
                }
                $format = isset($arguments['format']) ? $arguments['format'] : 'd/m/Y';
                $timestamp = strtotime($arguments['date']);
                if ($timestamp === false) {
                    throw new Exception('Formato de data inválido');
                }
                return array(
                    'operation' => 'format',
                    'original_date' => $arguments['date'],
                    'result' => date($format, $timestamp)
                );

            case 'add_days':
                if (!isset($arguments['date']) || !isset($arguments['days'])) {
                    throw new Exception('Data e número de dias são obrigatórios');
                }
                $timestamp = strtotime($arguments['date']);
                if ($timestamp === false) {
                    throw new Exception('Formato de data inválido');
                }
                $days = (int) $arguments['days'];
                $newTimestamp = strtotime('+' . $days . ' days', $timestamp);
                return array(
                    'operation' => 'add_days',
                    'original_date' => $arguments['date'],
                    'days_added' => $days,
                    'result' => date('Y-m-d H:i:s', $newTimestamp)
                );

            case 'diff_days':
                if (!isset($arguments['date']) || !isset($arguments['date2'])) {
                    throw new Exception('Duas datas são obrigatórias para calcular diferença');
                }
                $timestamp1 = strtotime($arguments['date']);
                $timestamp2 = strtotime($arguments['date2']);
                if ($timestamp1 === false || $timestamp2 === false) {
                    throw new Exception('Formato de data inválido');
                }
                $diff = ($timestamp2 - $timestamp1) / (60 * 60 * 24);
                return array(
                    'operation' => 'diff_days',
                    'date1' => $arguments['date'],
                    'date2' => $arguments['date2'],
                    'difference_days' => (int) $diff
                );

            default:
                throw new Exception('Operação não suportada: ' . $operation);
        }
    }
}

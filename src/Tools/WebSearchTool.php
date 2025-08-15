<?php

namespace Ollama\Tools;

/**
 * Tool para busca na web
 * Exemplo de implementação de uma tool que simula busca web
 */
class WebSearchTool extends AbstractTool
{
    /**
     * Obtém o nome da tool
     *
     * @return string
     */
    public function getName()
    {
        return 'web_search';
    }

    /**
     * Obtém a descrição da tool
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Buscar informações na web usando uma query específica';
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
                'query' => array(
                    'type' => 'string',
                    'description' => 'Consulta de busca'
                ),
                'max_results' => array(
                    'type' => 'integer',
                    'description' => 'Número máximo de resultados (1-20)',
                    'minimum' => 1,
                    'maximum' => 20,
                    'default' => 5
                )
            ),
            'required' => array('query')
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
        $query = isset($arguments['query']) ? trim($arguments['query']) : '';
        $maxResults = isset($arguments['max_results']) ? (int) $arguments['max_results'] : 5;

        if (empty($query)) {
            return json_encode(array(
                'error' => 'Query de busca é obrigatória'
            ));
        }

        // Limita o número de resultados entre 1 e 20
        $maxResults = max(1, min(20, $maxResults));

        // Simulação de resultados de busca
        // Em uma implementação real, você integraria com APIs como Google, Bing, etc.
        $results = $this->simulateWebSearch($query, $maxResults);

        return json_encode(array(
            'query' => $query,
            'total_results' => count($results),
            'results' => $results
        ));
    }

    /**
     * Simula resultados de busca web
     *
     * @param string $query
     * @param int $maxResults
     * @return array
     */
    private function simulateWebSearch($query, $maxResults)
    {
        $results = array();

        // Gera resultados simulados baseados na query
        for ($i = 1; $i <= $maxResults; $i++) {
            $results[] = array(
                'title' => 'Resultado ' . $i . ' para: ' . $query,
                'url' => 'https://exemplo.com/resultado-' . $i . '/' . urlencode(strtolower($query)),
                'snippet' => 'Este é um snippet simulado para o resultado ' . $i . ' da busca por "' . $query . '". Contém informações relevantes sobre o tópico pesquisado.',
                'domain' => 'exemplo.com'
            );
        }

        return $results;
    }
}

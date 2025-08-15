<?php

namespace Ollama;

use Ollama\Exceptions\OllamaException;
use Ollama\Http\HttpClient;
use Ollama\Models\Model;
use Ollama\Tools\ToolManager;

/**
 * Cliente principal para a API do Ollama
 */
class Ollama
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var ToolManager
     */
    private $toolManager;

    /**
     * @param string $baseUrl URL base do servidor Ollama (ex: http://localhost:11434)
     * @param array $options Opções adicionais para configuração
     */
    public function __construct($baseUrl = 'http://localhost:11434', array $options = array())
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = new HttpClient($this->baseUrl, $options);
        $this->toolManager = new ToolManager();
    }

    /**
     * Define o token de API para autenticação
     * Útil para APIs compatíveis com OpenAI ou instâncias do Ollama que requerem autenticação
     *
     * @param string $token Token de API
     * @return void
     */
    public function setApiToken($token)
    {
        $this->httpClient->setApiToken($token);
    }

    /**
     * Obtém o token de API configurado
     *
     * @return string|null
     */
    public function getApiToken()
    {
        return $this->httpClient->getApiToken();
    }

    /**
     * Gera uma completion para um prompt
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function generate(array $params, $streamCallback = null)
    {
        $endpoint = '/api/generate';
        
        $this->parseModelFromParams($params);

        if ($streamCallback !== null && isset($params['stream']) && $params['stream']) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }
        
        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Gera uma chat completion
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function chat(array $params, $streamCallback = null)
    {
        $endpoint = '/api/chat';

        $this->parseModelFromParams($params);
        
        if ($streamCallback !== null && isset($params['stream']) && $params['stream']) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }
        
        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Extrai o modelo dos parâmetros e o define no objeto Model
     *
     * @param array $params Parâmetros da requisição
     */
    private function parseModelFromParams(array &$params)
    {
        if (!empty($params['model']) && $params['model'] instanceof Model) {
            $model= $params['model'];
            $params['model'] = $model->getName();
            $params['options'] = (object) $model->getOptions();
            
            foreach ($model->getParameters() as $key => $value) {
                if (isset($params[$key])) {
                    continue; // Já existe no array
                }

                if (is_array($value)) {
                    $params[$key] = (object) $value;
                } else {
                    $params[$key] = $value;
                }
            }
        }
    }

    /**
     * Lista todos os modelos disponíveis localmente
     *
     * @return array
     * @throws OllamaException
     */
    public function listModels()
    {
        return $this->httpClient->get('/api/tags');
    }

    /**
     * Mostra informações detalhadas sobre um modelo
     *
     * @param string $model Nome do modelo
     * @param bool $verbose Se deve retornar informações detalhadas
     * @return array
     * @throws OllamaException
     */
    public function showModel($model, $verbose = false)
    {
        $params = array('model' => $model);
        if ($verbose) {
            $params['verbose'] = true;
        }
        
        return $this->httpClient->post('/api/show', $params);
    }

    /**
     * Copia um modelo
     *
     * @param string $source Nome do modelo de origem
     * @param string $destination Nome do modelo de destino
     * @return array
     * @throws OllamaException
     */
    public function copyModel($source, $destination)
    {
        $params = array(
            'source' => $source,
            'destination' => $destination
        );
        
        return $this->httpClient->post('/api/copy', $params);
    }

    /**
     * Deleta um modelo
     *
     * @param string $model Nome do modelo a ser deletado
     * @return array
     * @throws OllamaException
     */
    public function deleteModel($model)
    {
        $params = array('model' => $model);
        return $this->httpClient->delete('/api/delete', $params);
    }

    /**
     * Baixa um modelo do repositório
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function pullModel(array $params, $streamCallback = null)
    {
        $endpoint = '/api/pull';
        
        if ($streamCallback !== null && (!isset($params['stream']) || $params['stream'])) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }
        
        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Envia um modelo para o repositório
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function pushModel(array $params, $streamCallback = null)
    {
        $endpoint = '/api/push';
        
        if ($streamCallback !== null && (!isset($params['stream']) || $params['stream'])) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }
        
        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Cria um novo modelo
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @return array
     * @throws OllamaException
     */
    public function createModel(array $params, $streamCallback = null)
    {
        $endpoint = '/api/create';
        
        if ($streamCallback !== null && (!isset($params['stream']) || $params['stream'])) {
            return $this->httpClient->postStream($endpoint, $params, $streamCallback);
        }
        
        return $this->httpClient->post($endpoint, $params);
    }

    /**
     * Gera embeddings para texto
     *
     * @param array $params Parâmetros da requisição
     * @return array
     * @throws OllamaException
     */
    public function embeddings(array $params)
    {
        $this->parseModelFromParams($params);

        return $this->httpClient->post('/api/embed', $params);
    }

    /**
     * Lista modelos em execução na memória
     *
     * @return array
     * @throws OllamaException
     */
    public function listRunningModels()
    {
        return $this->httpClient->get('/api/ps');
    }

    /**
     * Obtém a versão do Ollama
     *
     * @return array
     * @throws OllamaException
     */
    public function version()
    {
        return $this->httpClient->get('/api/version');
    }

    /**
     * Verifica se um blob existe
     *
     * @param string $digest Digest SHA256 do blob
     * @return bool
     * @throws OllamaException
     */
    public function blobExists($digest)
    {
        try {
            $this->httpClient->head('/api/blobs/' . $digest);
            return true;
        } catch (OllamaException $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Envia um blob para o servidor
     *
     * @param string $digest Digest SHA256 esperado
     * @param string $data Dados do arquivo
     * @return array
     * @throws OllamaException
     */
    public function pushBlob($digest, $data)
    {
        return $this->httpClient->put('/api/blobs/' . $digest, $data, array(
            'Content-Type: application/octet-stream'
        ));
    }

    /**
     * Obtém o cliente HTTP para uso avançado
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Obtém o gerenciador de tools
     *
     * @return ToolManager
     */
    public function getToolManager()
    {
        return $this->toolManager;
    }

    /**
     * Registra uma nova tool
     *
     * @param \Ollama\Tools\ToolInterface $tool
     * @return void
     */
    public function registerTool($tool)
    {
        $this->toolManager->registerTool($tool);
    }

    /**
     * Executa uma tool pelo nome
     *
     * @param string $toolName
     * @param array $arguments
     * @return string
     */
    public function executeTool($toolName, array $arguments)
    {
        return $this->toolManager->executeTool($toolName, $arguments);
    }

    /**
     * Lista todas as tools disponíveis
     *
     * @return array
     */
    public function listAvailableTools()
    {
        return $this->toolManager->listTools();
    }

    /**
     * Obtém tools no formato esperado pela API
     *
     * @return array
     */
    public function getToolsForAPI()
    {
        return $this->toolManager->jsonSerialize();
    }

    /**
     * Gera uma chat completion com suporte a tools
     *
     * @param array $params Parâmetros da requisição
     * @param callable|null $streamCallback Callback para streaming (opcional)
     * @param bool $enableTools Se deve incluir tools na requisição
     * @return array
     * @throws OllamaException
     */
    public function chatWithTools(array $params, $streamCallback = null, $enableTools = true)
    {
        if ($enableTools) {
            $tools = $this->getToolsForAPI();
            if (!empty($tools)) {
                $params['tools'] = $tools;
            }
        }

        return $this->chat($params, $streamCallback);
    }
}

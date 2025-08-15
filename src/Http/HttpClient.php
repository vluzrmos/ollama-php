<?php

namespace Ollama\Http;

use Ollama\Exceptions\HttpException;
use Ollama\Exceptions\OllamaException;

/**
 * Cliente HTTP para comunicação com a API do Ollama
 */
class HttpClient
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * @var string|null
     */
    private $apiToken;

    /**
     * @param string $baseUrl
     * @param array $options
     */
    public function __construct($baseUrl, array $options = array())
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->defaultOptions = array_merge(array(
            'timeout' => 300,
            'connect_timeout' => 30,
            'user_agent' => 'Ollama/1.0',
            'verify_ssl' => true
        ), $options);
        
        // Extrair token de API das opções se fornecido
        if (isset($options['api_token'])) {
            $this->apiToken = $options['api_token'];
        }
    }

    /**
     * Define o token de API para autenticação
     *
     * @param string $token Token de API
     * @return void
     */
    public function setApiToken($token)
    {
        $this->apiToken = $token;
    }

    /**
     * Obtém o token de API configurado
     *
     * @return string|null
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Realiza uma requisição GET
     *
     * @param string $endpoint
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function get($endpoint, array $headers = array())
    {
        return $this->request('GET', $endpoint, null, $headers);
    }

    /**
     * Realiza uma requisição POST
     *
     * @param string $endpoint
     * @param array|string $data
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function post($endpoint, $data = null, array $headers = array())
    {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Realiza uma requisição POST com streaming
     *
     * @param string $endpoint
     * @param array $data
     * @param callable $callback
     * @return array
     * @throws OllamaException
     */
    public function postStream($endpoint, array $data, $callback)
    {
        $url = $this->baseUrl . $endpoint;
        $jsonData = json_encode($data);
        
        if ($jsonData === false) {
            throw new OllamaException('Falha ao codificar dados JSON');
        }

        $ch = curl_init();
        
        // Preparar headers
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        );
        
        // Adicionar token de autorização se disponível
        if ($this->apiToken) {
            $headers[] = 'Authorization: Bearer ' . $this->apiToken;
        }
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => $this->defaultOptions['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->defaultOptions['connect_timeout'],
            CURLOPT_USERAGENT => $this->defaultOptions['user_agent'],
            CURLOPT_SSL_VERIFYPEER => $this->defaultOptions['verify_ssl'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_WRITEFUNCTION => function($ch, $data) use ($callback) {
                $lines = explode("\n", $data);

                foreach ($lines as $line) {
                    $line = trim($line);

                    if(mb_substr($line, 0, 12) === 'data: [DONE]') {
                        return strlen($data);
                    }

                    if (mb_substr($line, 0, 6) === 'data: ') {
                        $line = mb_substr($line, 6);
                    }

                    if (!empty($line)) {
                        $decoded = json_decode($line, true);
                        if ($decoded !== null) {
                            call_user_func($callback, $decoded);
                        }
                    }
                }
                return strlen($data);
            }
        ));

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($success === false) {
            throw new HttpException('Erro cURL: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new HttpException('Erro HTTP: ' . $httpCode);
        }

        return array('success' => true);
    }

    /**
     * Realiza uma requisição PUT
     *
     * @param string $endpoint
     * @param array|string $data
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function put($endpoint, $data, array $headers = array())
    {
        return $this->request('PUT', $endpoint, $data, $headers);
    }

    /**
     * Realiza uma requisição DELETE
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function delete($endpoint, array $data = null, array $headers = array())
    {
        return $this->request('DELETE', $endpoint, $data, $headers);
    }

    /**
     * Realiza uma requisição HEAD
     *
     * @param string $endpoint
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function head($endpoint, array $headers = array())
    {
        return $this->request('HEAD', $endpoint, null, $headers, false);
    }

    /**
     * Realiza uma requisição HTTP
     *
     * @param string $method
     * @param string $endpoint
     * @param mixed $data
     * @param array $headers
     * @param bool $expectBody
     * @return array
     * @throws OllamaException
     */
    private function request($method, $endpoint, $data = null, array $headers = array(), $expectBody = true)
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $curlOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->defaultOptions['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->defaultOptions['connect_timeout'],
            CURLOPT_USERAGENT => $this->defaultOptions['user_agent'],
            CURLOPT_SSL_VERIFYPEER => $this->defaultOptions['verify_ssl'],
            CURLOPT_CUSTOMREQUEST => $method
        );

        // Headers padrão
        $defaultHeaders = array();
        
        // Adicionar token de autorização se disponível
        if ($this->apiToken) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->apiToken;
        }
        
        // Preparar dados se necessário - sempre enviar como JSON
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $jsonData = json_encode($data);
                
                if ($jsonData === false) {
                    throw new OllamaException('Falha ao codificar dados JSON');
                }

                $data = $jsonData;
            }
            
            // Sempre definir Content-Type como JSON quando há dados
            $defaultHeaders[] = 'Content-Type: application/json';
            $curlOptions[CURLOPT_POSTFIELDS] = $data;
        }

        // Merge headers
        $allHeaders = array_merge($defaultHeaders, $headers);
        if (!empty($allHeaders)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $allHeaders;
        }

        // Configurações específicas por método
        switch ($method) {
            case 'HEAD':
                $curlOptions[CURLOPT_NOBODY] = true;
                break;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new HttpException('Erro cURL: ' . $error);
        }

        // Para requisições HEAD, só precisamos verificar o código HTTP
        if ($method === 'HEAD') {
            if ($httpCode >= 400) {
                throw new HttpException('Erro HTTP: ' . $httpCode, $httpCode);
            }
            return array('http_code' => $httpCode);
        }

        // Verificar código HTTP
        if ($httpCode >= 400) {
            $errorData = null;
            if (!empty($response)) {
                $errorData = json_decode($response, true);
            }
            throw new HttpException('Erro HTTP: ' . $httpCode, $httpCode, null, $errorData);
        }

        // Decodificar resposta JSON se esperamos um corpo
        if ($expectBody && !empty($response)) {
            $decoded = json_decode($response, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new OllamaException('Falha ao decodificar resposta JSON: ' . json_last_error_msg());
            }
            return $decoded;
        }

        return array('http_code' => $httpCode, 'body' => $response);
    }
}

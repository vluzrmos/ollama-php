<?php

namespace Vluzrmos\Ollama\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Vluzrmos\Ollama\Exceptions\HttpException;
use Vluzrmos\Ollama\Exceptions\OllamaException;

/**
 * HTTP Client for communicating with the Ollama API using Guzzle
 */
class HttpClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Sets whether to always concatenate path to base URL
     *
     * @var boolean
     */
    private $alwaysConcatenatePath = true;

    /**
     * @var string|null
     */
    private $apiToken;

    /**
     * @param string $baseUrl
     * @param array $options
     */
    public function __construct($baseUrl, array $options = [])
    {
        $this->setBaseUrl($baseUrl);

        // Default options
        $defaultOptions = [
            'timeout' => 300,
            'connect_timeout' => 30,
            'verify' => true,
            'headers' => [
                'User-Agent' => 'Ollama/1.0'
            ]
        ];

        // Merge with provided options
        $clientOptions = array_merge($defaultOptions, $options);

        // Extract API token from options if provided
        if (isset($options['api_token'])) {
            $this->apiToken = $options['api_token'];
        }

        // Set base_uri for Guzzle
        $clientOptions['base_uri'] = $this->baseUrl;

        $this->client = new Client($clientOptions);
    }

    public function setBaseUrl($baseUrl)
    {
        if ($this->alwaysConcatenatePath) {
            $baseUrl = rtrim($baseUrl, '/').'/';
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * Sets whether to always concatenate path to base URL
     *
     * @param boolean $concatenate
     * @return void
     */
    public function setAlwaysConcatenatePath($concatenate = true)
    {
        $this->alwaysConcatenatePath = (bool) $concatenate;
    }

    /**
     * Sets the API token for authentication
     *
     * @param string $token API token
     * @return void
     */
    public function setApiToken($token)
    {
        $this->apiToken = $token;
    }

    /**
     * Gets the configured API token
     *
     * @return string|null
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Build headers array with authentication if available
     *
     * @param array $additionalHeaders
     * @return array
     */
    private function buildHeaders(array $additionalHeaders = [])
    {
        $headers = $additionalHeaders;
        
        if ($this->apiToken) {
            $headers['Authorization'] = 'Bearer ' . $this->apiToken;
        }

        return $headers;
    }

    /**
     * Handle Guzzle exceptions and convert to appropriate exception types
     *
     * @param RequestException $e
     * @throws HttpException
     * @throws OllamaException
     */
    private function handleException(RequestException $e)
    {
        if ($e instanceof ConnectException) {
            throw new HttpException('Connection Error: ' . $e->getMessage(), 0, $e);
        }

        if ($e instanceof ClientException || $e instanceof ServerException) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            
            $errorData = null;
            if (!empty($body)) {
                $decoded = json_decode($body, true);
                if ($decoded !== null) {
                    $errorData = $decoded;
                }
            }
            
            throw new HttpException('HTTP Error: ' . $statusCode, $statusCode, $e, $errorData);
        }

        throw new OllamaException('Request Error: ' . $e->getMessage(), 0, $e);
    }

    public function request($method, $endpoint, $options = [])
    {
        if ($this->alwaysConcatenatePath) {
            $endpoint = ltrim($endpoint, '/');
        }

        $options['headers'] = $this->buildHeaders(isset($options['headers']) ? $options['headers'] : []);
        
        return $this->client->request($method, $endpoint, $options);
    }

    /**
     * Makes a GET request
     *
     * @param string $endpoint
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function get($endpoint, array $headers = [])
    {
        try {
            $response = $this->request('GET', $endpoint);

            return $this->processResponse($response);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Makes a POST request
     *
     * @param string $endpoint
     * @param array|string $data
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function post($endpoint, $data = null, array $headers = [])
    {
        try {
            $options = [];

            if ($data !== null) {
                $options['headers']['Content-Type'] = 'application/json';
                $options['json'] = $data;
            }

            $response = $this->request('POST', $endpoint, $options);

            return $this->processResponse($response);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Process HTTP response and decode JSON
     *
     * @param ResponseInterface $response
     * @return array
     * @throws OllamaException
     */
    private function processResponse(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        
        if (empty($body)) {
            return ['http_code' => $response->getStatusCode(), 'body' => ''];
        }

        $decoded = json_decode($body, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new OllamaException('Failed to decode JSON response: ' . json_last_error_msg());
        }
        
        return $decoded ?: ['http_code' => $response->getStatusCode(), 'body' => $body];
    }

    /**
     * Makes a POST request with streaming
     *
     * @param string $endpoint
     * @param array $data
     * @param callable $callback
     * @return array
     * @throws OllamaException
     */
    public function postStream($endpoint, array $data, $callback)
    {
        try {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $data,
                'stream' => true
            ];

            $response = $this->request('POST', $endpoint, $options);
            $body = $response->getBody();

            while (!$body->eof()) {
                $line = $this->readLine($body);
                $line = trim($line);

                if (mb_substr($line, 0, 12) === 'data: [DONE]') {
                    break;
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

            $body->close();
            return ['success' => true];
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Read a line from stream
     *
     * @param mixed $stream
     * @return string
     */
    private function readLine($stream)
    {
        $line = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }
        return $line;
    }

    /**
     * Makes a PUT request
     *
     * @param string $endpoint
     * @param array|string $data
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function put($endpoint, $data, array $headers = [])
    {
        try {
            $options = [];

            if ($data !== null) {
                $options['headers']['Content-Type'] = 'application/json';
                $options['json'] = $data;
            }

            $response = $this->request('PUT', $endpoint, $options);

            return $this->processResponse($response);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Makes a DELETE request
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function delete($endpoint, array $data = null, array $headers = [])
    {
        try {
            $options = [];

            if ($data !== null) {
                $options['headers']['Content-Type'] = 'application/json';
                $options['json'] = $data;
            }

            $response = $this->request('DELETE', $endpoint, $options);

            return $this->processResponse($response);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Makes a HEAD request
     *
     * @param string $endpoint
     * @param array $headers
     * @return array
     * @throws OllamaException
     */
    public function head($endpoint, array $headers = [])
    {
        try {
            $response = $this->request('HEAD', $endpoint, [
                'headers' => $headers
            ]);

            return ['http_code' => $response->getStatusCode()];
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }
}

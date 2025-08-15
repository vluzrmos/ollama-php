# Ollama PHP 5.6 Client

Um cliente PHP 5.6 para a API do Ollama, permitindo integração com modelos de linguagem local.

## Requisitos

- PHP 5.6 ou superior
- Extensão cURL
- Extensão JSON

## Instalação

```bash
composer require vluzr/ollama-php56
```

## Uso Básico

### Configuração do Cliente

```php
<?php
require_once 'vendor/autoload.php';

use Ollama\OllamaClient;

// Ollama local sem autenticação
$client = new OllamaClient('http://localhost:11434');

// Ollama com token de API
$client = new OllamaClient('http://localhost:11434', [
    'api_token' => 'seu-token-aqui'
]);

// OpenAI API (compatível)
$client = new OllamaClient('https://api.openai.com/v1', [
    'api_token' => 'sk-sua-chave-openai'
]);

// Configuração dinâmica
$client = new OllamaClient('http://localhost:11434');
$client->setApiToken(getenv('OLLAMA_API_KEY'));
```

## Autenticação

### Ollama Local (sem token)
```php
$client = new OllamaClient('http://localhost:11434');
```

### Ollama com Token
```php
$client = new OllamaClient('http://localhost:11434', [
    'api_token' => 'seu-token-ollama'
]);
```

### OpenAI API
```php
$client = new OllamaClient('https://api.openai.com/v1', [
    'api_token' => 'sk-sua-chave-openai'
]);
```

### Configuração Dinâmica
```php
$client = new OllamaClient('http://localhost:11434');

// Configurar token depois
$client->setApiToken('novo-token');

// Verificar token atual
$token = $client->getApiToken();
```

### Variáveis de Ambiente
```php
$client = new OllamaClient('http://localhost:11434');
$client->setApiToken(getenv('OLLAMA_API_TOKEN'));
```

### Gerar Completions

```php
$response = $client->generate([
    'model' => 'llama3.2',
    'prompt' => 'Por que o céu é azul?',
    'stream' => false
]);

echo $response['response'];
```

### Chat Completion

```php
$response = $client->chat([
    'model' => 'llama3.2',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Olá, como você está?'
        ]
    ],
    'stream' => false
]);

echo $response['message']['content'];
```

### Listar Modelos

```php
$models = $client->listModels();
foreach ($models['models'] as $model) {
    echo $model['name'] . "\n";
}
```

### Embeddings

```php
$embeddings = $client->embeddings([
    'model' => 'all-minilm',
    'input' => 'Texto para gerar embeddings'
]);
```

## Funcionalidades Suportadas

- ✅ Generate completion
- ✅ Chat completion 
- ✅ List models
- ✅ Show model information
- ✅ Pull model
- ✅ Push model
- ✅ Create model
- ✅ Delete model
- ✅ Copy model
- ✅ Generate embeddings
- ✅ List running models
- ✅ Version information
- ✅ Tool calling
- ✅ Structured outputs
- ✅ Image support (multimodal)
- ✅ API Token authentication
- ✅ OpenAI API compatibility

## Compatibilidade

Este cliente é compatível com:
- **Ollama** (local ou remoto, com ou sem autenticação)
- **OpenAI API** 
- **Qualquer API compatível com OpenAI** (Anthropic, etc.)
- **Servidores Ollama com autenticação personalizada**

## Exemplos Avançados

### Chat com Histórico

```php
$messages = [
    ['role' => 'user', 'content' => 'Por que o céu é azul?'],
    ['role' => 'assistant', 'content' => 'devido ao espalhamento Rayleigh.'],
    ['role' => 'user', 'content' => 'Como isso é diferente do espalhamento Mie?']
];

$response = $client->chat([
    'model' => 'llama3.2',
    'messages' => $messages,
    'stream' => false
]);
```

### Streaming Responses

```php
$client->generate([
    'model' => 'llama3.2',
    'prompt' => 'Conte-me uma história',
    'stream' => true
], function($chunk) {
    echo $chunk['response'];
});
```

### Tool Calling

```php
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_weather',
            'description' => 'Obter o clima de uma cidade',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'city' => [
                        'type' => 'string',
                        'description' => 'A cidade para obter o clima'
                    ]
                ],
                'required' => ['city']
            ]
        ]
    ]
];

$response = $client->chat([
    'model' => 'llama3.2',
    'messages' => [
        ['role' => 'user', 'content' => 'Qual é o clima em São Paulo?']
    ],
    'tools' => $tools,
    'stream' => false
]);
```

## Licença

MIT

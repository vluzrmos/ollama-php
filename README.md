````markdown
# Ollama PHP Client

Cliente PHP para a API do Ollama, compatível com PHP 5.6+. Esta biblioteca fornece uma interface fácil de usar para interagir com o servidor Ollama e também inclui compatibilidade com a API OpenAI.

## Características

- ✅ Compatível com PHP 5.6+
- ✅ Suporte completo à API nativa do Ollama
- ✅ **Nova**: Compatibilidade com OpenAI API endpoints
- ✅ **Nova**: Classe `Model` para configuração reutilizável de modelos
- ✅ Chat completions com histórico
- ✅ Streaming de respostas
- ✅ Suporte a imagens (modelos de visão como Llava)
- ✅ Function calling (tools)
- ✅ Embeddings
- ✅ Gerenciamento de modelos
- ✅ Tratamento de erros robusto
- ✅ Documentação completa

## Instalação

```bash
composer require vluzrrmos/ollama-php
```

## Uso Rápido

### Cliente Ollama (API Nativa)

```php
<?php
require_once 'vendor/autoload.php';

use Ollama\Ollama;
use Ollama\Models\Message;
use Ollama\Models\Model;

// Criar cliente
$ollama = new Ollama('http://localhost:11434');

// Chat simples
$response = $ollama->chat([
    'model' => 'llama3.2',
    'messages' => [
        Message::system('Você é um assistente útil.')->toArray(),
        Message::user('Olá!')->toArray()
    ]
]);

echo $response['message']['content'];
```

### Cliente OpenAI (Compatível)

```php
<?php
use Ollama\OpenAI;
use Ollama\Models\Model;

// Criar cliente OpenAI compatível
$openai = new OpenAI('http://localhost:11434', 'ollama');

// Chat usando métodos OpenAI
$response = $openai->chat('llama3.2', [
    $openai->systemMessage('Você é um assistente útil.'),
    $openai->userMessage('Olá!')
]);

echo $response['choices'][0]['message']['content'];
```

### Classe Model para Reutilização

```php
<?php
use Ollama\Models\Model;

// Criar modelo configurado
$model = Model::llama32()
    ->setTemperature(0.8)
    ->setTopP(0.9)
    ->setNumCtx(4096)
    ->setSeed(42);

// Usar com OpenAI client
$response = $openai->chat($model, [
    $openai->userMessage('Conte uma história')
]);

// Ou usar com Ollama client
$params = array_merge($model->toArray(), [
    'messages' => [Message::user('Conte uma história')->toArray()]
]);
$response = $ollama->chat($params);
```

## Exemplos Avançados

### Streaming

```php
// Com cliente Ollama
$ollama->generate([
    'model' => 'llama3.2',
    'prompt' => 'Conte uma história',
    'stream' => true
], function($chunk) {
    echo $chunk['response'] ?? '';
});

// Com cliente OpenAI
$openai->chatStream('llama3.2', [
    $openai->userMessage('Conte uma história')
], function($chunk) {
    echo $chunk['choices'][0]['delta']['content'] ?? '';
});
```

### Modelos com Visão (Imagens)

```php
// Com cliente OpenAI
$response = $openai->chat('llava', [
    $openai->imageMessage(
        'O que você vê nesta imagem?',
        'data:image/png;base64,iVBORw0KGg...'
    )
]);
```

### Function Calling (Tools)

```php
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_weather',
            'description' => 'Obtém informações do clima',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'Localização'
                    ]
                ],
                'required' => ['location']
            ]
        ]
    ]
];

$response = $openai->chatCompletions([
    'model' => 'llama3.2',
    'messages' => [
        $openai->userMessage('Como está o clima em São Paulo?')
    ],
    'tools' => $tools
]);
```

### JSON Mode

```php
$response = $openai->chat('llama3.2', [
    $openai->systemMessage('Responda sempre em JSON válido.'),
    $openai->userMessage('Liste 3 cores primárias')
], [
    'response_format' => $openai->jsonFormat()
]);
```

### Embeddings

```php
// Ollama
$response = $ollama->embeddings([
    'model' => 'all-minilm',
    'input' => 'Texto para embedding'
]);

// OpenAI
$response = $openai->embed('all-minilm', [
    'Primeiro texto',
    'Segundo texto'
]);
```

## Modelos Pré-configurados

A classe `Model` oferece métodos estáticos para modelos populares:

```php
// Modelos pré-configurados
$llama32 = Model::llama32();
$llama31 = Model::llama31();
$mistral = Model::mistral();
$codellama = Model::codellama();
$llava = Model::llava();

// Modelo customizado
$custom = Model::custom('meu-modelo', [
    'temperature' => 0.7,
    'top_p' => 0.9
]);
```

## Compatibilidade OpenAI

Esta biblioteca implementa os seguintes endpoints da OpenAI API:

- ✅ `/v1/chat/completions`
- ✅ `/v1/completions` 
- ✅ `/v1/embeddings`
- ✅ `/v1/models`
- ✅ `/v1/models/{model}`

### Parâmetros Suportados

#### Chat Completions
- `model`, `messages`, `temperature`, `top_p`, `max_tokens`
- `stream`, `stream_options`, `stop`, `seed`
- `frequency_penalty`, `presence_penalty`
- `response_format` (JSON mode)
- `tools` (function calling)

#### Completions
- `model`, `prompt`, `temperature`, `top_p`, `max_tokens`
- `stream`, `stream_options`, `stop`, `seed`, `suffix`
- `frequency_penalty`, `presence_penalty`

#### Embeddings
- `model`, `input` (string ou array)

## Gerenciamento de Modelos

```php
// Listar modelos
$models = $ollama->listModels();

// Baixar modelo
$ollama->pullModel(['model' => 'llama3.2']);

// Informações do modelo
$info = $ollama->showModel('llama3.2');

// Deletar modelo
$ollama->deleteModel('modelo-antigo');
```

## Tratamento de Erros

```php
use Ollama\Exceptions\OllamaException;

try {
    $response = $ollama->chat([
        'model' => 'modelo-inexistente',
        'messages' => [Message::user('Olá')->toArray()]
    ]);
} catch (OllamaException $e) {
    echo "Erro: " . $e->getMessage();
    echo "Código: " . $e->getCode();
}
```

## Configuração

### Variáveis de Ambiente

```bash
export OLLAMA_API_URL=http://localhost:11434
export OLLAMA_API_TOKEN=seu-token-aqui
```

### Opções do Cliente

```php
$ollama = new Ollama('http://localhost:11434', [
    'timeout' => 60,
    'connect_timeout' => 10,
    'verify_ssl' => false
]);

$openai = new OpenAI('http://localhost:11434', 'ollama', [
    'timeout' => 120
]);
```

## Requisitos

- PHP >= 5.6.0
- ext-curl
- ext-json

## Exemplos Completos

Veja os arquivos de exemplo na pasta `examples/`:

- [`basic_usage.php`](examples/basic_usage.php) - Uso básico da API Ollama
- [`openai_usage.php`](examples/openai_usage.php) - Exemplos com API OpenAI
- [`advanced_chat.php`](examples/advanced_chat.php) - Chat avançado com tools

## Licença

MIT License. Veja [LICENSE](LICENSE) para detalhes.

## Contribuições

Contribuições são bem-vindas! Veja [CONTRIBUTING.md](CONTRIBUTING.md) para guidelines.

````

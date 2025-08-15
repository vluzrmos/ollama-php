# API Reference

## OllamaClient

### Constructor

```php
new OllamaClient($baseUrl = 'http://localhost:11434', array $options = array())
```

**Parameters:**
- `$baseUrl` (string): URL base do servidor Ollama
- `$options` (array): Opções de configuração

**Options disponíveis:**
- `timeout` (int): Timeout da requisição em segundos (padrão: 60)
- `connect_timeout` (int): Timeout de conexão em segundos (padrão: 10)
- `user_agent` (string): User agent personalizado
- `verify_ssl` (bool): Verificar certificados SSL (padrão: true)
- `api_token` (string): Token de API para autenticação (Bearer token)

### Métodos

#### setApiToken()

```php
setApiToken(string $token): void
```

Define o token de API para autenticação. Útil para APIs compatíveis com OpenAI ou instâncias do Ollama que requerem autenticação.

#### getApiToken()

```php
getApiToken(): string|null
```

Obtém o token de API configurado.

#### generate()

```php
generate(array $params, callable $streamCallback = null): array
```

Gera uma completion para um prompt.

**Parameters:**
- `model` (string): Nome do modelo
- `prompt` (string): Prompt para gerar resposta
- `suffix` (string, opcional): Texto após a resposta do modelo
- `images` (array, opcional): Lista de imagens codificadas em base64
- `format` (string|array, opcional): Formato da resposta ("json" ou JSON schema)
- `options` (array, opcional): Parâmetros do modelo
- `system` (string, opcional): Mensagem do sistema
- `template` (string, opcional): Template do prompt
- `stream` (bool, opcional): Se deve fazer streaming
- `raw` (bool, opcional): Se deve usar modo raw
- `keep_alive` (string, opcional): Tempo para manter modelo em memória

#### chat()

```php
chat(array $params, callable $streamCallback = null): array
```

Gera uma chat completion.

**Parameters:**
- `model` (string): Nome do modelo
- `messages` (array): Array de mensagens
- `tools` (array, opcional): Lista de tools disponíveis
- `format` (string|array, opcional): Formato da resposta
- `options` (array, opcional): Parâmetros do modelo
- `stream` (bool, opcional): Se deve fazer streaming
- `keep_alive` (string, opcional): Tempo para manter modelo em memória

#### listModels()

```php
listModels(): array
```

Lista todos os modelos disponíveis localmente.

#### showModel()

```php
showModel(string $model, bool $verbose = false): array
```

Mostra informações detalhadas sobre um modelo.

#### copyModel()

```php
copyModel(string $source, string $destination): array
```

Copia um modelo.

#### deleteModel()

```php
deleteModel(string $model): array
```

Deleta um modelo.

#### pullModel()

```php
pullModel(array $params, callable $streamCallback = null): array
```

Baixa um modelo do repositório.

**Parameters:**
- `model` (string): Nome do modelo
- `insecure` (bool, opcional): Permitir conexões inseguras
- `stream` (bool, opcional): Se deve fazer streaming

#### pushModel()

```php
pushModel(array $params, callable $streamCallback = null): array
```

Envia um modelo para o repositório.

#### createModel()

```php
createModel(array $params, callable $streamCallback = null): array
```

Cria um novo modelo.

**Parameters:**
- `model` (string): Nome do novo modelo
- `from` (string, opcional): Nome do modelo base
- `files` (array, opcional): Arquivos para criar o modelo
- `template` (string, opcional): Template do prompt
- `system` (string, opcional): Prompt do sistema
- `quantize` (string, opcional): Tipo de quantização

#### embeddings()

```php
embeddings(array $params): array
```

Gera embeddings para texto.

**Parameters:**
- `model` (string): Nome do modelo
- `input` (string|array): Texto ou array de textos
- `truncate` (bool, opcional): Truncar se exceder contexto
- `options` (array, opcional): Parâmetros do modelo
- `keep_alive` (string, opcional): Tempo para manter modelo

#### listRunningModels()

```php
listRunningModels(): array
```

Lista modelos em execução na memória.

#### version()

```php
version(): array
```

Obtém a versão do Ollama.

#### blobExists()

```php
blobExists(string $digest): bool
```

Verifica se um blob existe no servidor.

#### pushBlob()

```php
pushBlob(string $digest, string $data): array
```

Envia um blob para o servidor.

## Models

### Message

Representa uma mensagem de chat.

```php
// Métodos estáticos
Message::user($content, $images = null)
Message::assistant($content)
Message::system($content)
Message::tool($content, $toolName)
```

### Tool

Representa uma tool para function calling.

```php
// Métodos estáticos
Tool::weather()
Tool::webSearch()
Tool::create($name, $description, $properties, $required = array())
```

### ModelInfo

Representa informações sobre um modelo.

```php
$info = new ModelInfo($data);
$info->getFormattedSize()
$info->getFormat()
$info->getFamily()
$info->getParameterSize()
$info->getQuantizationLevel()
```

## Utils

### ImageHelper

```php
ImageHelper::encodeImage($imagePath)
ImageHelper::encodeImages($imagePaths)
ImageHelper::isValidImage($imagePath)
ImageHelper::getImageInfo($imagePath)
```

### Validator

```php
Validator::isValidModelName($model)
Validator::isValidMessage($message)
Validator::isValidMessages($messages)
Validator::isValidTool($tool)
Validator::isValidSha256($digest)
Validator::isValidModelOptions($options)
Validator::sanitizeModelName($model)
```

## Exceptions

### OllamaException

Exceção base para todos os erros da biblioteca.

### HttpException

Exceção para erros HTTP.

### ValidationException

Exceção para erros de validação.

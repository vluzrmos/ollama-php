# Sistema de Tools - Ollama PHP 5.6

O sistema de tools permite que você defina funcionalidades customizadas que podem ser chamadas pelo modelo durante conversas. Este sistema foi projetado para ser flexível e extensível.

## Estrutura

### Interface `ToolInterface`

Todas as tools devem implementar a interface `ToolInterface`:

```php
interface ToolInterface
{
    public function getName();                    // Nome da tool
    public function getDescription();             // Descrição da tool
    public function getParametersSchema();        // Schema dos parâmetros
    public function execute(array $arguments);    // Execução da tool
    public function toArray();                    // Formato para API
}
```

### Classe Abstrata `AbstractTool`

Fornece implementação padrão do método `toArray()`:

```php
abstract class AbstractTool implements ToolInterface
{
    public function toArray()
    {
        return array(
            'type' => 'function',
            'function' => array(
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => $this->getParametersSchema()
            )
        );
    }
}
```

## Tools Disponíveis

### 1. WeatherTool
- **Nome**: `get_weather`
- **Função**: Obter informações de clima
- **Parâmetros**: `city` (obrigatório), `format` (celsius/fahrenheit)

### 2. CalculatorTool
- **Nome**: `calculator`
- **Função**: Operações matemáticas básicas
- **Parâmetros**: `operation` (add/subtract/multiply/divide), `a`, `b`

### 3. WebSearchTool
- **Nome**: `web_search`
- **Função**: Simular busca na web
- **Parâmetros**: `query` (obrigatório), `max_results` (1-20)

### 4. DateTimeTool
- **Nome**: `datetime_operations`
- **Função**: Operações com data/hora
- **Parâmetros**: `operation` (current/format/add_days/diff_days), outros conforme operação

## Como Criar Uma Tool Personalizada

### Exemplo Básico

```php
<?php

use Ollama\Tools\AbstractTool;

class MinhaToolPersonalizada extends AbstractTool
{
    public function getName()
    {
        return 'minha_tool';
    }

    public function getDescription()
    {
        return 'Descrição da minha tool personalizada';
    }

    public function getParametersSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'parametro1' => array(
                    'type' => 'string',
                    'description' => 'Descrição do parâmetro'
                ),
                'parametro2' => array(
                    'type' => 'integer',
                    'description' => 'Número inteiro',
                    'minimum' => 1,
                    'maximum' => 100
                )
            ),
            'required' => array('parametro1')
        );
    }

    public function execute(array $arguments)
    {
        $param1 = isset($arguments['parametro1']) ? $arguments['parametro1'] : '';
        $param2 = isset($arguments['parametro2']) ? (int) $arguments['parametro2'] : 1;

        // Sua lógica aqui
        $resultado = "Processando: {$param1} com valor {$param2}";

        return json_encode(array(
            'resultado' => $resultado,
            'status' => 'sucesso'
        ));
    }
}
```

## Uso com ToolManager

### Registro Manual

```php
use Ollama\Tools\ToolManager;

$toolManager = new ToolManager();

// Registrar tool personalizada
$toolManager->registerTool(new MinhaToolPersonalizada());

// Listar tools
$tools = $toolManager->listTools();

// Executar tool
$resultado = $toolManager->executeTool('minha_tool', array(
    'parametro1' => 'teste',
    'parametro2' => 42
));
```

## Uso com OllamaClient

### Configuração Automática

```php
use Ollama\OllamaClient;

// Cliente com tools padrão habilitadas
$client = new OllamaClient('http://localhost:11434');

// Cliente sem tools padrão
$client = new OllamaClient('http://localhost:11434', array(
    'disable_default_tools' => true
));
```

### Registro de Tools

```php
// Registrar tool personalizada
$client->registerTool(new MinhaToolPersonalizada());

// Listar tools disponíveis
$tools = $client->listAvailableTools();

// Executar tool diretamente
$resultado = $client->executeTool('minha_tool', $argumentos);
```

### Chat com Tools

```php
// Chat com tools habilitadas automaticamente
$response = $client->chatWithTools(array(
    'model' => 'llama2',
    'messages' => array(
        array(
            'role' => 'user',
            'content' => 'Que horas são e qual é 15 + 25?'
        )
    )
));

// Chat sem tools
$response = $client->chatWithTools($params, null, false);
```

## Schema de Parâmetros

O schema segue o padrão JSON Schema:

```php
array(
    'type' => 'object',
    'properties' => array(
        'nome_parametro' => array(
            'type' => 'string|number|integer|boolean|array|object',
            'description' => 'Descrição do parâmetro',
            'enum' => array('valor1', 'valor2'), // Para valores limitados
            'minimum' => 1,                       // Para números
            'maximum' => 100,                     // Para números
            'default' => 'valor_padrao'           // Valor padrão
        )
    ),
    'required' => array('parametros_obrigatorios')
);
```

## Tratamento de Erros

Tools devem retornar sempre JSON. Para erros:

```php
return json_encode(array(
    'error' => 'Descrição do erro',
    'code' => 'CODIGO_ERRO' // Opcional
));
```

Para sucesso:

```php
return json_encode(array(
    'resultado' => $dados,
    'status' => 'sucesso'
));
```

## Melhores Práticas

1. **Validação**: Sempre valide os parâmetros de entrada
2. **Documentação**: Forneça descrições claras para tools e parâmetros
3. **Tratamento de Erro**: Implemente tratamento adequado de erros
4. **Performance**: Mantenha as tools rápidas e eficientes
5. **Segurança**: Valide e sanitize dados de entrada
6. **Compatibilidade**: Mantenha compatibilidade com PHP 5.6

## Exemplos Completos

Veja os arquivos:
- `examples/tools_example.php` - Exemplo básico
- `examples/advanced_tools_example.php` - Exemplo avançado
- `src/Tools/` - Implementações de referência

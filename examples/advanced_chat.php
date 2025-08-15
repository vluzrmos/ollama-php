<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;
use Ollama\Models\Message;
use Ollama\Tools\ToolManager;
use Ollama\Utils\ImageHelper;

/**
 * Exemplo avançado: Sistema de chat interativo com tools
 */
class ChatSystem
{
    private $client;
    private $messages;

    /**
     * @var ToolManager
     */
    private $tools;

    public function __construct()
    {
        $this->client = new OllamaClient(getenv('OLLAMA_API_URL') ?: 'http://localhost:11434');
        $this->messages = array();
        $this->setupTools();
    }

    private function setupTools()
    {
        $this->tools = new ToolManager();

        $this->tools->registerDefaultTools();
    }

    public function addSystemMessage($content)
    {
        $this->messages[] = Message::system($content);
    }

    public function chat($userMessage, $model = 'qwen2.5:3b', array $images = null)
    {
        // Adicionar mensagem do usuário
        $this->messages[] = Message::user($userMessage, $images);

        try {
            $params = array(
                'model' => $model,
                'messages' => $this->prepareMessages(),
                'stream' => false
            );

            if (!$images) {
                $params['tools'] = $this->tools->jsonSerialize();
            }

            $response = $this->client->chat($params);

            $assistantMessage = $response['message'];

            // Verificar se o modelo quer usar tools
            if (isset($assistantMessage['tool_calls']) && !empty($assistantMessage['tool_calls'])) {
                return $this->handleToolCalls($assistantMessage['tool_calls'], $model);
            } else {
                // Adicionar resposta do assistente ao histórico
                $this->messages[] = Message::assistant($assistantMessage['content']);
                return $assistantMessage['content'];
            }
        } catch (Exception $e) {
            return "Erro: " . $e->getMessage();
        }
    }

    private function handleToolCalls($toolCalls, $model)
    {
        $results = array();

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = $toolCall['function']['arguments'];

            // Executar a função
            $result = $this->executeFunction($functionName, $arguments, $model);

            // Adicionar resultado como mensagem de tool
            $this->messages[] = Message::tool($result, $functionName);
            $results[] = "Resultado de $functionName: $result";
        }

        // Fazer nova chamada para o modelo com os resultados das tools
        try {
            $response = $this->client->chat(array(
                'model' => $model,
                'messages' => $this->prepareMessages(),
                'tools' => $this->tools,
                'stream' => false
            ));

            $finalMessage = $response['message']['content'];
            $this->messages[] = Message::assistant($finalMessage);

            return $finalMessage;
        } catch (Exception $e) {
            return "Erro ao processar resultado das tools: " . $e->getMessage();
        }
    }

    private function executeFunction($functionName, $arguments)
    {
        if ($this->tools->hasTool($functionName)) {
            $tool = $this->tools->getTool($functionName);
            return $tool->execute($arguments);
        }

        throw new InvalidArgumentException("Tool '$functionName' não registrada.");
    }

    private function prepareMessages()
    {
        return array_map(function ($message) {
            return $message->toArray();
        }, $this->messages);
    }

    public function getConversationHistory()
    {
        return $this->messages;
    }

    public function clearHistory()
    {
        $this->messages = array();
    }
}

// Exemplo de uso
echo "=== Sistema de Chat Interativo com Tools ===\n\n";

$chatSystem = new ChatSystem();
$chatSystem->addSystemMessage('Você é um assistente útil que pode usar ferramentas para obter informações sobre clima, fazer cálculos e obter data/hora atual. Sempre responda em português.');

// Simulação de conversa
$conversations = array(
    "Qual é o clima em São Paulo hoje?",
    "Quanto é 15 + 27?",
    "Que horas são agora?",
    "Calcule a raiz quadrada de 144",
    "Como está o tempo no Rio de Janeiro em Fahrenheit?",
    "Obrigado pela ajuda!"
);

foreach ($conversations as $userInput) {
    echo "Usuário: $userInput\n";
    $response = $chatSystem->chat($userInput);
    echo "Assistente: $response\n\n";

    // Pequena pausa para simular conversa real
    sleep(1);
}

echo "=== Exemplo de Chat com Imagens ===\n";

$response = $chatSystem->chat('O que há na imagem?', 'qwen2.5vl:3b', [ImageHelper::encodeImage(__DIR__.'/sample.png')]);
echo "Resposta: $response\n\n";

$response = $chatSystem->chat('Quantas pessoas há na imagem?', 'qwen2.5vl:3b', [ImageHelper::encodeImage(__DIR__.'/sample.png')]);
echo "Resposta: $response\n\n";

echo "=== Histórico da Conversa ===\n";
$history = $chatSystem->getConversationHistory();
foreach ($history as $i => $message) {
    echo ($i + 1) . ". [{$message->role}]: {$message->content}\n";
    if ($message->toolName) {
        echo "   (Tool: {$message->toolName})\n";
    }
}

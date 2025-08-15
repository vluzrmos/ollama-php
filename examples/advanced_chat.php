<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ollama\OllamaClient;
use Ollama\Models\Message;
use Ollama\Models\Tool;

/**
 * Exemplo avançado: Sistema de chat interativo com tools
 */
class ChatSystem
{
    private $client;
    private $messages;
    private $tools;

    public function __construct()
    {
        $this->client = new OllamaClient('http://localhost:11434');
        $this->messages = array();
        $this->setupTools();
    }

    private function setupTools()
    {
        $this->tools = array(
            Tool::weather()->toArray(),
            Tool::create(
                'calculate',
                'Realizar cálculos matemáticos',
                array(
                    'expression' => array(
                        'type' => 'string',
                        'description' => 'Expressão matemática para calcular'
                    )
                ),
                array('expression')
            )->toArray(),
            Tool::create(
                'get_time',
                'Obter data e hora atual',
                array(),
                array()
            )->toArray()
        );
    }

    public function addSystemMessage($content)
    {
        $this->messages[] = Message::system($content);
    }

    public function chat($userMessage)
    {
        // Adicionar mensagem do usuário
        $this->messages[] = Message::user($userMessage);

        try {
            // Enviar para o modelo
            $response = $this->client->chat(array(
                'model' => 'llama3.2',
                'messages' => $this->prepareMessages(),
                'tools' => $this->tools,
                'stream' => false
            ));

            $assistantMessage = $response['message'];

            // Verificar se o modelo quer usar tools
            if (isset($assistantMessage['tool_calls']) && !empty($assistantMessage['tool_calls'])) {
                return $this->handleToolCalls($assistantMessage['tool_calls']);
            } else {
                // Adicionar resposta do assistente ao histórico
                $this->messages[] = Message::assistant($assistantMessage['content']);
                return $assistantMessage['content'];
            }

        } catch (Exception $e) {
            return "Erro: " . $e->getMessage();
        }
    }

    private function handleToolCalls($toolCalls)
    {
        $results = array();

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = $toolCall['function']['arguments'];

            // Executar a função
            $result = $this->executeFunction($functionName, $arguments);

            // Adicionar resultado como mensagem de tool
            $this->messages[] = Message::tool($result, $functionName);
            $results[] = "Resultado de $functionName: $result";
        }

        // Fazer nova chamada para o modelo com os resultados das tools
        try {
            $response = $this->client->chat(array(
                'model' => 'llama3.2',
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
        switch ($functionName) {
            case 'get_weather':
                return $this->getWeather($arguments['city'], isset($arguments['format']) ? $arguments['format'] : 'celsius');
                
            case 'calculate':
                return $this->calculate($arguments['expression']);
                
            case 'get_time':
                return $this->getTime();
                
            default:
                return "Função desconhecida: $functionName";
        }
    }

    private function getWeather($city, $format = 'celsius')
    {
        // Simulação de API de clima
        $weatherData = array(
            'São Paulo' => array('temp' => 23, 'condition' => 'ensolarado'),
            'Rio de Janeiro' => array('temp' => 28, 'condition' => 'parcialmente nublado'),
            'Brasília' => array('temp' => 25, 'condition' => 'nublado')
        );

        $city = ucwords(strtolower($city));
        if (isset($weatherData[$city])) {
            $temp = $weatherData[$city]['temp'];
            if ($format === 'fahrenheit') {
                $temp = ($temp * 9/5) + 32;
            }
            return "O clima em $city está {$weatherData[$city]['condition']} com temperatura de {$temp}°" . 
                   ($format === 'celsius' ? 'C' : 'F');
        }
        
        return "Dados meteorológicos não disponíveis para $city";
    }

    private function calculate($expression)
    {
        // Sanitizar expressão para segurança
        $expression = preg_replace('/[^0-9+\-*\/\(\)\.\s]/', '', $expression);
        
        try {
            $result = eval("return $expression;");
            return "O resultado de '$expression' é: $result";
        } catch (Exception $e) {
            return "Erro no cálculo: expressão inválida";
        }
    }

    private function getTime()
    {
        return "Data e hora atual: " . date('d/m/Y H:i:s');
    }

    private function prepareMessages()
    {
        return array_map(function($message) {
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

echo "=== Histórico da Conversa ===\n";
$history = $chatSystem->getConversationHistory();
foreach ($history as $i => $message) {
    echo ($i + 1) . ". [{$message->role}]: {$message->content}\n";
    if ($message->toolName) {
        echo "   (Tool: {$message->toolName})\n";
    }
}

<?php

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Bahia');

use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Tools\BrasilAPI\BrasilAPI;
use Vluzrmos\Ollama\Tools\BrasilAPI\CEPInfoTool;
use Vluzrmos\Ollama\Tools\BrasilAPI\CNPJInfoTool;
use Vluzrmos\Ollama\Tools\ToolManager;
use Vluzrmos\Ollama\Tools\BrasilAPI\FeriadosNacionaisTool;
use Vluzrmos\Ollama\Tools\TimeTool;

$brasilApi = new BrasilAPI();

$cepInfoTool = new CEPInfoTool($brasilApi);
$cnpjInfoTool = new CNPJInfoTool($brasilApi);
$feriadosNacionaisTool = new FeriadosNacionaisTool($brasilApi);

$openai = new OpenAI(getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1', 'ollama');

$model = new Model(getenv('TEST_MODEL') ?: 'qwen2.5:3b');

$toolManager = new ToolManager();
$toolManager->registerTool(new TimeTool());
$toolManager->registerTool($cepInfoTool);
$toolManager->registerTool($cnpjInfoTool);
$toolManager->registerTool($feriadosNacionaisTool);

$questions = [
    'What is the address of the postal code 01001-000?',
    'What is the name of the company with CNPJ 13.927.801/0001-49?',
    'List the national holidays in Brazil for the year 2025.',
    'September 7 of year 2025 is a holiday in Brazil? If so, what holiday is it?',
    'What time is it?',
];

foreach ($questions as $question) {
    echo "User: $question\n";

    $messages = [
        Message::system(
            implode(PHP_EOL, [
                'Você é um assistente útil que ajuda os usuários a obter informações usando ferramentas (tools) disponíveis.',
                'Sempre responder em português do brasil, ou português.',
                'A data e hora atual é ' . date('Y-m-d H:i:s T') . '.',
            ])
        ),
        Message::user($question)
    ];

    $response = $openai->chatCompletions([
        'model' => $model,
        'messages' => $messages,
        'tools' => $toolManager,
    ]);

    if (isset($response['choices'][0]['message']['tool_calls'])) {
        $results = $toolManager->executeToolCalls($response['choices'][0]['message']['tool_calls']);

        $resultsMessages = $toolManager->toolCallResultsToMessages($results);

        $messages = array_merge($messages, [$response['choices'][0]['message']], $resultsMessages);

        $response = $openai->chatCompletions([
            'model' => $model,
            'messages' => $messages,
            'tools' => $toolManager
        ]);

        echo "=== Final Response ===\n";
        echo $response['choices'][0]['message']['role'] . ': ' . $response['choices'][0]['message']['content'] . PHP_EOL . PHP_EOL;
    } else {
        echo "No tool calls found in the response." . PHP_EOL . PHP_EOL;
    }
}

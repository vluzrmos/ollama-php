<?php

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Bahia');

use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Tools\BrasilAPI\BrasilAPI;
use Vluzrmos\Ollama\Tools\BrasilAPI\CEPInfoTool;
use Vluzrmos\Ollama\Tools\BrasilAPI\CNPJInfoTool;
use Vluzrmos\Ollama\Tools\ToolManager;

$brasilApi = new BrasilAPI();

$cepInfoTool = new CEPInfoTool($brasilApi);
$cnpjInfoTool = new CNPJInfoTool($brasilApi);

$openai = new OpenAI(getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1', 'ollama');

$model = new Model(getenv('TEST_MODEL') ?: 'qwen2.5:3b');

$toolManager = new ToolManager();
$toolManager->registerTool($cepInfoTool);
$toolManager->registerTool($cnpjInfoTool);

$questions = [
    'What is the address of the postal code 01001-000?',
    'What is the name of the company with CNPJ 13.927.801/0001-49?'
];

foreach ($questions as $question) {
    echo "User: $question\n";

    $messages = [
        [
            'role' => 'system',
            'content' => implode(PHP_EOL, [
                'Você é um assistente útil que ajuda os usuários a obter informações sobre códigos postais (CEP) e empresas (CNPJ) no Brasil usando ferramentas especializadas.',
                'Sempre responder em português do brasil, ou português.'
            ])
        ],
        [
            'role' => 'user',
            'content' => $question
        ]
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
        echo "No tool calls found in the response.". PHP_EOL . PHP_EOL;
    }
}

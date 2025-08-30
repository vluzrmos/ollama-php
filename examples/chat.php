<?php

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Bahia');

use Vluzrmos\Ollama\Chat\Chat;
use Vluzrmos\Ollama\Chat\OpenAIClientAdapter;
use Vluzrmos\Ollama\OpenAI;
use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Tools\BrasilAPI\FeriadosNacionaisTool;
use Vluzrmos\Ollama\Tools\TimeTool;

$openai = new OpenAI(getenv('OPENAI_API_URL') ?: 'http://localhost:11434/v1', 'ollama');
$clientAdapter = new OpenAIClientAdapter($openai);

$model = new Model(getenv('TEST_MODEL') ?: 'qwen2.5:3b');

$chat = new Chat($clientAdapter);

$chat->withModel($model);

$brasilApi = new \Vluzrmos\Ollama\Tools\BrasilAPI\BrasilAPI();

$chat->registerTools([
    new TimeTool(),
    new FeriadosNacionaisTool($brasilApi)
]);

// Chat will retain the messages history and execute call tools if registered

$chat->chat([
    Message::system(
        implode(PHP_EOL, [
            'You are a helpful assistant that helps users find information.',
            'Always respond in English.',
        ])
    ),
    Message::user('What time is it?'),
    ['role' => 'user', 'content' => 'What is the date today?']
]);

$chat->chat([
    Message::user('List the national holidays in Brazil for september/2025.'),
]);

function print_all_messages_from_chat(Chat $chat)
{
    echo "All chat messages:\n";

    foreach ($chat->getMessages()->all() as $message) {
        echo $message->role . ': ' . $message->content . PHP_EOL;
    }
}

print_all_messages_from_chat($chat);

<?php

$files = [
    __DIR__.'/basic_usage.php',
    __DIR__.'/advanced_chat.php',
    __DIR__.'/openai_usage.php',
    __DIR__.'/simple_tool_test.php',
    __DIR__.'/tool_execution_demo.php',
];


foreach ($files as $file) {
    echo "=============================".PHP_EOL;
    echo "Running example: $file".PHP_EOL;
    echo "=============================".PHP_EOL;

    include $file;
    
    echo PHP_EOL.PHP_EOL;
}
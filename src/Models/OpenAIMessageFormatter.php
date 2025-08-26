<?php

namespace Vluzrmos\Ollama\Models;

class OpenAIMessageFormatter implements MessageFormatter
{
    public function format(Message $message)
    {
        $data = array(
            'role' => $message->role,
            'content' => $message->content,
        );

        if (!empty($message->images)) {
            $data['content'] = [
                [
                    'type' => 'text',
                    'text' => $message->content
                ]
            ];

            foreach ($message->images as $image) {
                $data['content'][] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $image
                    ]
                ];
            }
        }

        if ($message->toolCalls !== null) {
            $data['tool_calls'] = $message->toolCalls;
        }

        if ($message->toolName !== null) {
            $data['tool_name'] = $message->toolName;
        }

        if ($message->thinking !== null) {
            $data['thinking'] = $message->thinking;
        }

        return $data;
    }
}

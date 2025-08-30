<?php

namespace Vluzrmos\Ollama\Chat;

use Vluzrmos\Ollama\Models\Message;
use Vluzrmos\Ollama\Models\MessageArrayStore;
use Vluzrmos\Ollama\Models\MessageStore;
use Vluzrmos\Ollama\Models\Model;
use Vluzrmos\Ollama\Models\ResponseMessage;
use Vluzrmos\Ollama\Tools\ToolInterface;
use Vluzrmos\Ollama\Tools\ToolManager;

class Chat
{
    /**
     * @var ClientAdapter
     */
    protected $client;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var MessageStore
     */
    protected $messages;

    /**
     * @var ToolManager
     */
    protected $tools;

    public function __construct(ClientAdapter $client,  MessageStore $messages = null, ToolManager $tools = null)
    {
        $this->client = $client;
        $this->tools = $tools ?: new ToolManager();
        $this->messages = $messages ?: new MessageArrayStore();
    }

    public function withModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }


    public function getModel()
    {
        return $this->model;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getTools()
    {
        return $this->tools;
    }

    /**
     * @param Message|array $message Message instance or associative array representing a Message
     * @return self
     */
    public function addMessage($message)
    {
        if ($message instanceof Message) {
            $this->messages->add($message);

            return $this;
        }

        if (is_array($message) && isset($message['role'])) {
            $this->messages->add(Message::fromArray($message));

            return $this;
        }

        throw new \InvalidArgumentException('Message must be an instance of Message or an associative array representing a Message.');
    }

    public function registerTool(ToolInterface $tool)
    {
        $this->tools->registerTool($tool);

        return $this;
    }

    /**
     * @param ToolInterface[] $tools
     * @return self
     */
    public function registerTools(array $tools)
    {
        foreach ($tools as $tool) {
            $this->registerTool($tool);
        }

        return $this;
    }

    /**
     * @param Message|MessageStore|array<mixed,Message>|array<mixed, array{"role": string}> $messages
     * @return self
     */
    public function addMessages($messages)
    {
        if ($messages instanceof Message) {
            return $this->addMessage($messages);
        }

        if ($messages instanceof MessageStore) {
            foreach ($messages->all() as $msg) {
                $this->addMessage($msg);
            }

            return $this;
        }

        if (is_array($messages)) {
            if (isset($messages['role'])) {
                $this->addMessage($messages);
                return $this;
            }

            foreach ($messages as $msg) {
                if ($msg instanceof MessageStore) {
                    foreach ($msg->all() as $msg) {
                        $this->addMessage($msg);
                    }

                    continue;
                }

                if ($msg instanceof Message || isset($msg['role'])) {
                    $this->addMessage($msg);
                    continue;
                }

                throw new \InvalidArgumentException('Each message in the array must be an instance of Message or an associative array representing a Message.');
            }
        }

        return $this;
    }
    /**
     *
     * @param Message|MessageStore|array<mixed,Message> $messages
     * @param Model|null $model
     * @param array $params
     * @return ResponseMessage
     */
    public function chat($messages = null, Model $model = null, array $params = [])
    {
        if ($messages !== null) {
            $this->addMessages($messages);
        }

        $model = $model ?: $this->model;

        if (!$model) {
            throw new \InvalidArgumentException('Model must be provided either in constructor or in chat method.');
        }

        if ($this->messages->count() === 0) {
            throw new \InvalidArgumentException('At least one message must be provided.');
        }

        $params['tools'] = $this->tools->jsonSerialize();

        $response = $this->client->chatCompletions(
            $model,
            $this->messages->all(),
            $params
        );
        
        $hasTools = false;

        foreach ($response->getMessages() as $msg) {
            $this->addMessage($msg);

            if ($msg->hasToolCalls()) {
                $toolsResults = $this->tools->executeToolCalls($msg->toolCalls);

                foreach ($this->tools->toolCallResultsToMessages($toolsResults) as $toolMessage) {
                    $this->addMessage($toolMessage);

                    $hasTools = true;
                }
            }
        }

        if ($hasTools) {
            return $this->chat(null, $model, $params);
        }

        return $response;
    }
}

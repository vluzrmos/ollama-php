<?php

namespace Vluzrmos\Ollama\Tools;

use Vluzrmos\Ollama\Models\Message;

class ToolCallResult
{
    /**
     * @var string
     */
    protected $toolName;

    /**
     * @var string|null
     */
    protected $toolCallId;

    /**
     * @var mixed
     */
    protected $result;
    
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string|null
     */

    protected $errorMessage;

    /**
     * ToolCallResult constructor.
     *
     * @param string $toolName
     * @param mixed $result
     * @param bool $success
     * @param string|null $errorMessage
     * @param string|null $toolCallId Optional Tool Call ID
     */
    public function __construct($toolName, $result, $success = true, $errorMessage = null, $toolCallId = null)
    {
        $this->toolName = $toolName;
        $this->result = $result;
        $this->success = $success;
        $this->errorMessage = $errorMessage;
        $this->toolCallId = $toolCallId;
    }

    /**
     * Checks if the tool call was successful
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * Gets the tool name
     *
     * @return string
     */
    public function getToolName()
    {
        return $this->toolName;
    }

    /**
     * Gets the tool call ID
     *
     * @return string|null
     */
    public function getToolCallId()
    {
        return $this->toolCallId;
    }

    /**
     * Gets the result of the tool call
     *
     * @return mixed
     */

    public function getResult()
    {
        return $this->result;
    }

    /**
     * Gets the error message if the tool call failed
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Sets the tool call ID
     *
     * @param string|null $toolCallId
     * @return $this
     */
    public function setToolCallId($toolCallId)
    {
        $this->toolCallId = $toolCallId;

        return $this;
    }

    public function setToolName($toolName)
    {
        $this->toolName = $toolName;

        return $this;
    }

    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    public function getResultString()
    {
        return is_string($this->result) ? $this->result : json_encode($this->result);
    }

    public function getMessageContentString()
    {
        $content = $this->isSuccess() ? $this->getResultString() : $this->getErrorMessage();

        if ($content === null) {
            $content = '';
        }

        return $content;
    }

    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function toMessage($role = 'tool')
    {
        $content = $this->getMessageContentString();

        $message = new Message(
            $role,
            $content
        );

        $message->toolCallId = $this->toolCallId;
        $message->toolName = $this->toolName;

        return $message;
    }

    public function toArray()
    {
        return [
            'tool_name' => $this->toolName,
            'tool_call_id' => $this->toolCallId,
            'result' => $this->result,
            'success' => $this->success,
            'error_message' => $this->errorMessage,
        ];
    }

    public static function fromArray(array $data)
    {
        $toolName = isset($data['tool_name']) ? $data['tool_name'] : (isset($data['toolName']) ? $data['toolName'] : null);
        $toolCallId = isset($data['tool_call_id']) ? $data['tool_call_id'] : (isset($data['toolCallId']) ? $data['toolCallId'] : null);
        $result = isset($data['result']) ? $data['result'] : null;
        $success = isset($data['success']) ? (bool)$data['success'] : false;
        $errorMessage = isset($data['error_message']) ? $data['error_message'] : (isset($data['errorMessage']) ? $data['errorMessage'] : (isset($data['error']) ? $data['error'] : null));

        return new self($toolName, $result, $success, $errorMessage, $toolCallId);
    }
}

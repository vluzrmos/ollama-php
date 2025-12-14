<?php

namespace Vluzrmos\Ollama\Tools;

use ArrayObject;
use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

class TimeTool extends AbstractTool
{
    protected $timezone;

    protected $defaultTimezone = 'UTC';

    public function getName()
    {
        return 'get_current_time';
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getDescription()
    {
        if ($this->timezone) {
            return 'Gets the current time in the timezone: ' . $this->timezone;
        }

        return 'Gets the current time in a specified timezone';
    }

    public function getParametersSchema()
    {
        if ($this->timezone) {
            return [
                'type' => 'object',
                'properties' => new ArrayObject([])
            ];
        }

        return [
            'type' => 'object',
            'properties' => [
                'timezone' => [
                    'type' => 'string',
                    'description' => 'The timezone to get the current time for, e.g. "America/New_York". Default is '.$this->defaultTimezone.'.'
                ]
            ]
        ];
    }

    public function execute(array $parameters)
    {
        if (empty($parameters['timezone'])) {
            $parameters['timezone'] = $this->timezone ?: $this->defaultTimezone;
        }

        try {
            $timezone = new \DateTimeZone($parameters['timezone']);
        } catch (\Exception $e) {
            throw new ToolExecutionException('Invalid timezone provided: ' . $parameters['timezone']);
        }

        try {
            $dateTime = new \DateTime('now', $timezone);
            
            return $dateTime->format('Y-m-d H:i:s T');
        } catch (\Exception $e) {
            throw new ToolExecutionException('Failed to get current time.');
        }
    }
}

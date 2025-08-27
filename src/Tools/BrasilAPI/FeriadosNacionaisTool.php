<?php

namespace Vluzrmos\Ollama\Tools\BrasilAPI;

use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

class FeriadosNacionaisTool extends AbstractBrasilAPITool
{

    public function getName()
    {
        return 'feriados_nacionais';
    }

    public function getDescription()
    {
        return 'Get the list of national holidays in Brazil for a given year.';
    }

    public function getParametersSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'year' => [
                    'type' => 'integer',
                    'description' => 'The year to retrieve the national holidays for. Format: YYYY (e.g., 2023).'
                ]
            ],
            'required' => ['year']
        ];   
    }

    public function execute(array $arguments)
    {
        $year = $arguments['year'];
        $maxYear = date('Y') + 100;

        if (!is_int($year) || $year < 1900 || $year > $maxYear) {
            throw new ToolExecutionException('Invalid year format. It should be an integer between 1900 and 2100.');
        }

        try {
            $response = $this->client->get("/feriados/v1/{$year}");
        } catch (\Exception $e) {
            throw new ToolExecutionException("Error when trying to fetch national holidays for the year: " . $year);
        }

        if (isset($response['http_code']) &&  $response['http_code'] !== 200) {
            throw new ToolExecutionException("Error code [{$response['http_code']}] when trying to fetch national holidays for the year: " . $year);
        }

        return $response;
    }
}

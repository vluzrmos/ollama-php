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
                ],
                'month' => [
                    'type' => 'integer',
                    'description' => '(Optional) The month to filter the national holidays. Format: MM (e.g., 1 for January, 12 for December). If not provided, all months will be returned.',
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

        $month = null;

        if (!empty($arguments['month'])) {
            $month = (int) $arguments['month'];

            if ($month > 12) {
                throw new ToolExecutionException('Invalid month format. It should be an integer between 1 and 12.');
            }

            if ($month < 1) {
                $month = null;
            }
        }

        try {
            $response = $this->client->get("/feriados/v1/{$year}");
        } catch (\Exception $e) {
            throw new ToolExecutionException("Error when trying to fetch national holidays for the year: " . $year);
        }

        if (isset($response['http_code']) &&  $response['http_code'] !== 200) {
            throw new ToolExecutionException("Error code [{$response['http_code']}] when trying to fetch national holidays for the year: " . $year);
        }

        if ($month && isset($response[0]['date'])) {
            $response = array_filter($response, function ($holiday) use ($month) {
                $dateParts = explode('-', $holiday['date']);
                return isset($dateParts[1]) && (int)$dateParts[1] === $month;
            });

            $response = array_values($response);
        }

        return $response;
    }
}

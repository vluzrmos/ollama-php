<?php

namespace Vluzrmos\Ollama\Tools\BrasilAPI;

use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

class CNPJInfoTool extends AbstractBrasilAPITool
{
    public function getName()
    {
        return 'cnpj_info';
    }

    public function getDescription()
    {
        return 'Get information about a Brazilian company by its CNPJ number.';
    }

    public function getParametersSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'cnpj' => [
                    'type' => 'string',
                    'description' => 'The Brazilian company registration number (CNPJ) to look up. Format: 00.000.000/0000-00 or 00000000000000.'
                ]
            ],
            'required' => ['cnpj']
        ];   
    }

    public function execute(array $arguments)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $arguments['cnpj']);

        if (strlen($cnpj) !== 14) {
            throw new ToolExecutionException('Invalid CNPJ format. It should contain exactly 14 digits.');
        }

        try {
            $response = $this->client->get("/cnpj/v1/{$cnpj}");
        } catch (\Exception $e) {
            throw new ToolExecutionException("Error when trying to fetch CNPJ information: " . $cnpj);
        }

        if (isset($response['http_code']) &&  $response['http_code'] !== 200) {
            throw new ToolExecutionException("Error code [{$response['http_code']}] when trying to fetch CNPJ information: " . $cnpj);
        }

        unset($response['service']);

        return $response;
    }

}

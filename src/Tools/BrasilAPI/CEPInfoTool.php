<?php

namespace Vluzrmos\Ollama\Tools\BrasilAPI;

use Vluzrmos\Ollama\Exceptions\ToolExecutionException;

class CEPInfoTool extends AbstractBrasilAPITool
{

    public function getName()
    {
        return 'cep_info';
    }

    public function getDescription()
    {
        return 'Get information about a Brazilian postal code (CEP).';
    }

    public function getParametersSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'cep' => [
                    'type' => 'string',
                    'description' => 'The Brazilian postal code (CEP) to look up. Format: 00000-000 or 00000000.'
                ]
            ],
            'required' => ['cep']
        ];   
    }

    public function execute(array $arguments)
    {
        $cep = preg_replace('/[^0-9]/', '', $arguments['cep']);

        if (strlen($cep) !== 8) {
            throw new ToolExecutionException('Invalid CEP format. It should contain exactly 8 digits.');
        }

        try {
            $response = $this->client->get("/cep/v1/{$cep}");
        } catch (\Exception $e) {
            throw new ToolExecutionException("Error when trying to fetch CEP information: " . $cep);
        }

        if (isset($response['http_code']) &&  $response['http_code'] !== 200) {
            throw new ToolExecutionException("Error code [{$response['http_code']}] when trying to fetch CEP information: " . $cep);
        }

        unset($response['service']);

        return $response;
    }
}

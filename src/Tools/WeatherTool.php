<?php

namespace Ollama\Tools;

/**
 * Tool para obter informações de clima
 * Exemplo de implementação de uma tool funcional
 */
class WeatherTool extends AbstractTool
{
    /**
     * Obtém o nome da tool
     *
     * @return string
     */
    public function getName()
    {
        return 'get_weather';
    }

    /**
     * Obtém a descrição da tool
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Obter informações do clima para uma cidade específica';
    }

    /**
     * Obtém o schema dos parâmetros da tool
     *
     * @return array
     */
    public function getParametersSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'city' => array(
                    'type' => 'string',
                    'description' => 'A cidade para obter o clima'
                ),
                'format' => array(
                    'type' => 'string',
                    'description' => 'Formato da temperatura (celsius ou fahrenheit)',
                    'enum' => array('celsius', 'fahrenheit'),
                    'default' => 'celsius'
                )
            ),
            'required' => array('city')
        );
    }

    /**
     * Executa a tool com os parâmetros fornecidos
     *
     * @param array $arguments Argumentos passados pelo modelo
     * @return string Resultado da execução
     */
    public function execute(array $arguments)
    {
        $city = isset($arguments['city']) ? $arguments['city'] : '';
        $format = isset($arguments['format']) ? $arguments['format'] : 'celsius';

        if (empty($city)) {
            return json_encode(array(
                'error' => 'Cidade é obrigatória'
            ));
        }

        // Simulação de uma API de clima
        // Em uma implementação real, você faria uma requisição para uma API externa
        $weatherData = $this->getWeatherData($city, $format);
        
        return json_encode($weatherData);
    }

    /**
     * Simula dados de clima para uma cidade
     *
     * @param string $city
     * @param string $format
     * @return array
     */
    private function getWeatherData($city, $format)
    {
        // Dados simulados
        $tempCelsius = rand(15, 35);
        $temp = ($format === 'fahrenheit') ? ($tempCelsius * 9/5) + 32 : $tempCelsius;
        $unit = ($format === 'fahrenheit') ? '°F' : '°C';

        $conditions = array('ensolarado', 'nublado', 'chuvoso', 'parcialmente nublado');
        $condition = $conditions[array_rand($conditions)];

        return array(
            'city' => $city,
            'temperature' => $temp . $unit,
            'condition' => $condition,
            'humidity' => rand(30, 80) . '%',
            'wind_speed' => rand(5, 25) . ' km/h'
        );
    }
}

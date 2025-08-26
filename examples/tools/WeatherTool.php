<?php

namespace Examples\Tools;

use Vluzrmos\Ollama\Exceptions\ToolExecutionException;
use Vluzrmos\Ollama\Tools\AbstractTool;

/**
 * Weather tool to demonstrate tool system usage
 * This is a mock implementation for demonstration
 */
class WeatherTool extends AbstractTool
{
    /**
     * Gets the tool name
     *
     * @return string
     */
    public function getName()
    {
        return 'get_current_weather';
    }

    /**
     * Gets the tool description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Gets current weather information for a location';
    }

    /**
     * Gets the tool parameter schema
     *
     * @return array
     */
    public function getParametersSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'location' => array(
                    'type' => 'string',
                    'description' => 'The city and state, e.g. São Paulo, SP'
                ),
                'unit' => array(
                    'type' => 'string',
                    'description' => 'Temperature unit',
                    'enum' => array('celsius', 'fahrenheit')
                )
            ),
            'required' => array('location')
        );
    }

    /**
     * Executes the tool with provided parameters
     *
     * @param array $arguments Arguments passed by the model
     * @return string Execution result in JSON format
     */
    public function execute(array $arguments)
    {
        echo basename(__FILE__, ".php") . ": Executing with arguments: " . json_encode($arguments) . PHP_EOL;

        if (!isset($arguments['location'])) {
            throw new ToolExecutionException("Missing required parameter: location");
        }

        $location = $arguments['location'];
        $unit = isset($arguments['unit']) ? $arguments['unit'] : 'celsius';

        // Mock data example based on location
        $weatherData = $this->getMockWeatherData($location, $unit);

        return $weatherData;
    }

    /**
     * Generates mock weather data based on location
     *
     * @param string $location
     * @param string $unit
     * @return array
     */
    private function getMockWeatherData($location, $unit)
    {
        // Mock data based on some known cities
        $mockData = array(
            'São Paulo' => array('temp' => 22, 'condition' => 'partly cloudy', 'humidity' => 65),
            'Rio de Janeiro' => array('temp' => 28, 'condition' => 'sunny', 'humidity' => 70),
            'Belo Horizonte' => array('temp' => 25, 'condition' => 'cloudy', 'humidity' => 60),
            'Salvador' => array('temp' => 30, 'condition' => 'sunny', 'humidity' => 75),
            'Recife' => array('temp' => 29, 'condition' => 'partly cloudy', 'humidity' => 80)
        );

        // Search for data based on city name (case-insensitive)
        $cityData = null;
        foreach ($mockData as $city => $data) {
            if (stripos($location, $city) !== false) {
                $cityData = $data;
                break;
            }
        }

        // If not found, use generic data
        if (!$cityData) {
            $cityData = array(
                'temp' => rand(15, 35),
                'condition' => 'variable',
                'humidity' => rand(40, 90)
            );
        }

        $temperature = $cityData['temp'];

        // Convert to Fahrenheit if requested
        if ($unit === 'fahrenheit') {
            $temperature = ($temperature * 9 / 5) + 32;
        }

        return array(
            'location' => $location,
            'temperature' => $temperature,
            'unit' => $unit,
            'condition' => $cityData['condition'],
            'humidity' => $cityData['humidity'],
            'timestamp' => date('Y-m-d H:i:s')
        );
    }
}

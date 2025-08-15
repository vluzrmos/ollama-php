<?php

namespace Ollama\Models;

/**
 * Representa informações sobre um modelo
 */
class ModelInfo
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $model;

    /**
     * @var string
     */
    public $modifiedAt;

    /**
     * @var int
     */
    public $size;

    /**
     * @var string
     */
    public $digest;

    /**
     * @var array
     */
    public $details;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->model = isset($data['model']) ? $data['model'] : '';
        $this->modifiedAt = isset($data['modified_at']) ? $data['modified_at'] : '';
        $this->size = isset($data['size']) ? $data['size'] : 0;
        $this->digest = isset($data['digest']) ? $data['digest'] : '';
        $this->details = isset($data['details']) ? $data['details'] : array();
    }

    /**
     * Obtém o tamanho formatado
     *
     * @return string
     */
    public function getFormattedSize()
    {
        $bytes = $this->size;
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtém o formato do modelo
     *
     * @return string
     */
    public function getFormat()
    {
        return isset($this->details['format']) ? $this->details['format'] : '';
    }

    /**
     * Obtém a família do modelo
     *
     * @return string
     */
    public function getFamily()
    {
        return isset($this->details['family']) ? $this->details['family'] : '';
    }

    /**
     * Obtém o tamanho dos parâmetros
     *
     * @return string
     */
    public function getParameterSize()
    {
        return isset($this->details['parameter_size']) ? $this->details['parameter_size'] : '';
    }

    /**
     * Obtém o nível de quantização
     *
     * @return string
     */
    public function getQuantizationLevel()
    {
        return isset($this->details['quantization_level']) ? $this->details['quantization_level'] : '';
    }
}

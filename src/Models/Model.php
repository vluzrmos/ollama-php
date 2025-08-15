<?php

namespace Ollama\Models;

/**
 * Classe para mapeamento de modelos e seus parâmetros
 * Facilita a reutilização e configuração de modelos
 */
class Model
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $name Nome do modelo
     * @param array $parameters Parâmetros do modelo
     * @param array $options Opções adicionais
     */
    public function __construct($name, array $parameters = array(), array $options = array())
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->options = $options;
    }

    /**
     * Obtém o nome do modelo
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define o nome do modelo
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Obtém todos os parâmetros
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Define os parâmetros
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Obtém um parâmetro específico
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * Define um parâmetro específico
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Remove um parâmetro
     *
     * @param string $key
     * @return $this
     */
    public function removeParameter($key)
    {
        if (isset($this->parameters[$key])) {
            unset($this->parameters[$key]);
        }
        return $this;
    }

    /**
     * Define a temperatura
     *
     * @param float $temperature
     * @return $this
     */
    public function setTemperature($temperature)
    {
        return $this->setParameter('temperature', $temperature);
    }

    /**
     * Obtém a temperatura
     *
     * @return float|null
     */
    public function getTemperature()
    {
        return $this->getParameter('temperature');
    }

    /**
     * Define o top_p
     *
     * @param float $topP
     * @return $this
     */
    public function setTopP($topP)
    {
        return $this->setParameter('top_p', $topP);
    }

    /**
     * Obtém o top_p
     *
     * @return float|null
     */
    public function getTopP()
    {
        return $this->getParameter('top_p');
    }

    /**
     * Define o top_k
     *
     * @param int $topK
     * @return $this
     */
    public function setTopK($topK)
    {
        return $this->setParameter('top_k', $topK);
    }

    /**
     * Obtém o top_k
     *
     * @return int|null
     */
    public function getTopK()
    {
        return $this->getParameter('top_k');
    }

    /**
     * Define o repeat_penalty
     *
     * @param float $repeatPenalty
     * @return $this
     */
    public function setRepeatPenalty($repeatPenalty)
    {
        return $this->setParameter('repeat_penalty', $repeatPenalty);
    }

    /**
     * Obtém o repeat_penalty
     *
     * @return float|null
     */
    public function getRepeatPenalty()
    {
        return $this->getParameter('repeat_penalty');
    }

    /**
     * Define o seed
     *
     * @param int $seed
     * @return $this
     */
    public function setSeed($seed)
    {
        return $this->setParameter('seed', $seed);
    }

    /**
     * Obtém o seed
     *
     * @return int|null
     */
    public function getSeed()
    {
        return $this->getParameter('seed');
    }

    /**
     * Define o num_ctx (tamanho do contexto)
     *
     * @param int $numCtx
     * @return $this
     */
    public function setNumCtx($numCtx)
    {
        return $this->setParameter('num_ctx', $numCtx);
    }

    /**
     * Obtém o num_ctx
     *
     * @return int|null
     */
    public function getNumCtx()
    {
        return $this->getParameter('num_ctx');
    }

    /**
     * Define o num_predict (número de tokens a prever)
     *
     * @param int $numPredict
     * @return $this
     */
    public function setNumPredict($numPredict)
    {
        return $this->setParameter('num_predict', $numPredict);
    }

    /**
     * Obtém o num_predict
     *
     * @return int|null
     */
    public function getNumPredict()
    {
        return $this->getParameter('num_predict');
    }

    /**
     * Define o stop words
     *
     * @param array $stop
     * @return $this
     */
    public function setStop(array $stop)
    {
        return $this->setParameter('stop', $stop);
    }

    /**
     * Obtém o stop words
     *
     * @return array|null
     */
    public function getStop()
    {
        return $this->getParameter('stop');
    }

    /**
     * Obtém as opções adicionais
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Define as opções adicionais
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Obtém uma opção específica
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Define uma opção específica
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Converte o modelo para array usado nas requisições
     *
     * @return array
     */
    public function toArray()
    {
        $result = array(
            'model' => $this->name
        );

        if (!empty($this->parameters)) {
            $result['options'] = $this->parameters;
        }

        $result = array_merge($result, $this->options);

        return $result;
    }

    public function __clone()
    {
        return new self($this->name, $this->parameters, $this->options);
    }
}

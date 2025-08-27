<?php

namespace Vluzrmos\Ollama\Tools\BrasilAPI;

use Vluzrmos\Ollama\Tools\AbstractTool;

abstract class AbstractBrasilAPITool extends AbstractTool
{
    /**
     * @var BrasilAPI
     */
    protected $client;

    /**
     * Constructor
     *
     * @param BrasilAPI $client
     */
    public function __construct(BrasilAPI $client)
    {
        $this->client = $client;    
    }

    /**
     * Get the BrasilAPI client
     *
     * @return BrasilAPI
     */
    public function getClient()
    {
        return $this->client;
    }
}

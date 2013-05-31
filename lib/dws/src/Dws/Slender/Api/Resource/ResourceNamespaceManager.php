<?php namespace Dws\Slender\Api\Resource;

use Dws\Utils;

class ResourceNamespaceManager 
{

    /**
    * configuration
    * @var array
    */
    protected $config;

    /**
    * __construct
    * @param array $config
    * @return void
    */
    public function __construct($config = null) 
    {
        $this->config = $config;
    }

    /**
    * getConfig
    * @return array
    */
    public function getConfig($name = null)
    {
        if ($name) return array_get($this->config, $name);
        return $this->config;    
    }

    /**
    * setConfig
    * @param array $config
    * @return Dws\Slender\Api\Resource\ResourceWriter
    */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;    
    }

    /**
    * tell me if the namesapce is valid
    */


}
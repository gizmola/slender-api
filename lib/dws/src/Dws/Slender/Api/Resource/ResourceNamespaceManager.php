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
    public function validPrefix($namespace, $suffix = null)
    {
        
        /*
        * append any provided suffix
        */
        $prefixes = array_map(function($x) use ($suffix) {
            $namespace = $suffix ? Utils\NamespaceHelper::extend($x, $suffix) : $x;
            return str_replace("\\", "\\\\", $namespace);
        }, $this->getConfig('valid-prefixes'));

        foreach ($prefixes as $pre) {

            $pattern = "/^$pre/";
            preg_match($pattern, $namespace, $matches);
            if (count($matches)) return true;

        }

        return false;

    }


}
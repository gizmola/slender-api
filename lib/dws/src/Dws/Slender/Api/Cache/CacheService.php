<?php namespace Dws\Slender\Api\Cache;


class CacheService
{

    protected $requestPath;
    protected $config;

    public function __construct($requestPath, $config)
    {
        $this->requestPath = $requestPath;
        $this->config = $config;
    }

    public function getRequestPath()
    {
        return $this->requestPath;    
    }

    public function setRequestPath($requestPath)
    {
        $this->requestPath = $requestPath;   
    }

    public function getConfig()
    {
        return $this->config;    
    }

    public function setConfig($config)
    {
        $this->config = $config;   
    }

}
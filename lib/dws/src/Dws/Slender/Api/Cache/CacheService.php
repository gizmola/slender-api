<?php namespace Dws\Slender\Api\Cache;


class CacheService
{

    protected $requestPath;
    protected $config;

    public function __construct($requestPath, $config, $params)
    {
        $this->requestPath = $requestPath;
        $this->config = $config;
        $this->params = $params;
    }

    public function getRequestPath()
    {
        return $this->requestPath;    
    }

    public function setRequestPath($requestPath)
    {
        $this->requestPath = $requestPath;   
    }

    public function getConfig($name = null)
    {
        
        if ($name) {
            return (!empty($this->config[$name])) ? $this->config[$name] : null;
        }

        return $this->config;    
    
    }

    public function setConfig($config)
    {
        $this->config = $config;   
    }

    public function setEnabled($bool)
    {
        $this->config['enabled'] = $bool;
    }

    public function enabled()
    {
        return $this->config['enabled'];
    }

    public function setPurge($bool) {
        $this->config['purge'] = $bool;    
    }

    public function purge()
    {
        return (!empty($this->config['purge'])) ? $this->config['purge'] : false;    
    }

    public function getParams()
    {
        return $this->params;    
    }

    public function setParams($params)
    {
        $this->params = $params;   
    }

    public function forget($query)
    {
        \Cache::forget($query);
    }

    public function buildRememberByFindMany()
    {
        /*
        * To distiunqish between ?foo=bar&a=b and ?a=b&foo=bar(same query)
        * we get the params as array, remove unused params, sort it and make it into a string again
        */
        $query = $this->getParams();
        unset($query['purge_cache']);
        unset($query['no_cache']);
        asort($query);
        return $this->getRequestPath() . http_build_query($query);
    }

    public function getData($rememberBy, $callback)
    {

        $cacheTime = $this->getConfig('cache_time');

        if ($this->purge()) {
            $this->forget($rememberBy);
        }

        return \Cache::remember($rememberBy , $cacheTime, $callback);

    }

    public function putData($rememberBy, $data)
    {
        $cacheTime = $this->getConfig('cache_time');
        \Cache::put($rememberBy, $data, $cacheTime);

    }

}
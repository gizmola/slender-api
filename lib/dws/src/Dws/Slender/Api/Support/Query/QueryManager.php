<?php namespace Dws\Slender\Api\Support\Query;

class QueryManager {
    
    protected $params;

    public function getParams()
    {
        return $this->params;
    }

    public function get($name)
    {
        return (!empty($this->params[$name])) ? $this->params[$name] : null;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

} 
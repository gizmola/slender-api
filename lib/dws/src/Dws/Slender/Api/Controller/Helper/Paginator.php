<?php namespace Dws\Slender\Api\Controller\Helper;

class Paginator
{

	/**
	* configuration data
	*
	* @var $config
	*/
	protected $config = [];

	/**
	* request
	* 
	* @var Illuminate\Http\Request $request
	*/
	protected $request;

	/**
	* the parameterbag
	*
	* @var Symfony\Component\HttpFoundation\ParameterBag $params
	*/
	protected $params;

	/**
	* the path portion of the request
	* @var string $path
	*/
	protected $path;

	/**
	* full url of request
	* string
	*/
	protected $url;
 
	public function __construct($config = null)
	{

		$this->config = $config ?: \Config::get('app.paginator');

	}

	/**
	* get config (by name)
	*
	* @param string $name
	* @return mixed
	*/
	public function getConfig($name = null)
	{
		if ($name) return array_get($this->config, $name);
		return $this->config;
	}

	/**
	* set config by name
	*
	* @param string $name
	* @return Dws\Slender\Api\Controller\Helper\Paginator
	*/
	public function setConfig($name, $val)
	{
		$this->config[$name] = $val;
		return $this;
	}
	
	/**
	* get the request
	*
	* @return Illuminate\Http\Request
	*/
	public function getRequest()
	{
		
		if (!isset($this->request))
			throw new \Exception("No request object has been set", 1);
			

		return $this->request;
	}

	/**
	* set config by name
	*
	* @param Illuminate\Http\Request $request
	* @return Dws\Slender\Api\Controller\Helper\Paginator
	*/
	public function setRequest($request)
	{
		$this->request = $request;
		return $this;
	}

	/**
	* set config by name
	*
	* @return string
	*/
	public function getPath()
	{
		
		if (!isset($this->path))
			$this->path = $this->getRequest()->path();	
		
		return $this->path;
	
	}

	/**
	* get the path portion of the request
	*
	* @param sting $path
	* @return Dws\Slender\Api\Controller\Helper\Paginator
	*/
	public function setPath($path)
	{
		
		$this->path = $path;
		return $this;
	
	}

	/**
	* get the query parameters
	*
	* @return Symfony\Component\HttpFoundation\ParameterBag
	*/
	public function getParams()
	{
		
		if (!isset($this->params))
			$this->params = $this->getRequest()->query;	
		
		return $this->params;
	
	}

	/**
	* get the full url
	*
	* @return string
	*/
	public function getUrl()
	{
		
		if (!isset($this->url))
			$this->url = $this->getRequest()->url();

		return $this->url;
	
	}

	/**
	* set the full url
	*
	* @param string $url
	*/
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	* get the next page url
	*
	* @return string
	*/
	public function next()
	{

        $params = $this->getParams();

        if ($params->has('take')) {

        	$perPage = $params->get('take');

        } else {

        	$perPage = $this->getConfig('per-page');

        }
        
        if ($params->has('skip')) {

            $skip = $params->get('skip') + $perPage; 
               
        } else {

            $skip = $perPage;

        }

        $params->set('skip', $skip); 
        $query = http_build_query($params->all());
        $next = $this->getUrl() . '?' . $query;
        
        return $next;
	
	}

	/**
	* root of site
	*
	* @return string
	*/
	protected function root()
	{
		return url('/');
	}


	

}
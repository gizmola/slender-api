<?php namespace Dws\Slender\Api\Model;


class Factory
{

	/**
	* array of resources
	* @var LMongo\LMongoManager
	*/
	protected $dbManager;

	/**
	* array of resources
	* @var array $resources
	*/
	protected $resources;

	/**
	*
	* construct
	* @param array $resources
	*/
	public function __construct($resources = null, $dbManager = null)
	{
		/*
		* if we don't get a resources use the IoC to create one
		*/
		$this->resources = $resources ?: 
			\App::make('config-manager')->getResourceConfig(); 

		/*
		* if we don't get a database manager use the IoC to create one
		*/
		$this->dbManager = $dbManager ?: 
			\App::make('mongo'); 		

	}

	/**
	* get the resource configuration [by name]
	*
	* @param string $name (optional)
	* @return mixed
	*/
	public function getConfig($name = null)
	{
		if (!is_null($name)) return array_get($this->resources, $name);
		return $this->resources;
	}

	/**
	* set the resource by name
	*
	* @param string $name
	* @param string $val
	* @return Dws\Slender\Api\Model\Factory
	*/
	public function setConfig($name, $val)
	{
		$this->resources[$name] = $name;
		return $this;
	}

	/**
	* search for the correct configuration
	* @param string $name
	* @param string $site
	*/
	public function findConfig($name, $site = null)
	{
		
		if (is_null($site)) {

			/*
			* without a site we must assume core
			*/
			$config = $this->getConfig("core.{$name}");

		} else {

			/*
			* first check for the extended site version
			*/
			$config = $this->getConfig("per-site.{$site}.{$name}");
			
			/*
			* fallback to a base
			*/
			if (is_null($config))
				$config = $this->getConfig($name);

		}

		if (!$config)
			throw new \Exception("Could not find config for $site $name", 1);

		return $config;

	}


	/**
	* get the database manager
	*
	* @return LMongo\LMongoManager
	*/
	public function getDbManager()
	{
		return $this->dbManager;
	}

	/**
	* set the database manager
	*
	* @param LMongo\LMongoManager $manager
	* @return Dws\Slender\Api\Model\Factory
	*/
	public function setDbManager($manager)
	{
		$this->dbManager = $manager;
		return $this;
	}

	/**
	* get the database via the manager
	*
	* @param string $site (optional)
	* @return mixed
	*/
	public function getConnection($site)
	{
		$site = $site ?: 'default';
		return $this->getDbManager()->connection($site);
	}

	/**
	* build a model
	* @param string $name
	* @param string $site
	*/
	public function build($name, $site = null, $relations = null)
	{
		if (!$relations) {

			$config = $this->findConfig($name, $site);
			$class = array_get($config, "model.class");

		} else {
			
			$config = $relations[$name];	
			$class = array_get($config, "class");
		
		}

		$connection = $this->getConnection($site);
		$model = new $class($connection);
		
		/*
		* set the models relations for downstream
		*/
		$relations = [
			'children' => array_get($config, "model.children"),	
			'parents' => array_get($config, "model.parents"),
		];

		$model->setRelations($relations);
		$model->setSite($site);
		return $model;

	}

}
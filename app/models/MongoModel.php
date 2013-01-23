<?php

class MongoModel{

	/**
	 * Collection for active model
	 *
	 * @var null
	 */
	public static $collection = null;

	/**
	 * Connection to use for active model
	 *
	 * @var null
	 */
	public $connection = null;


	public function __construct($connection = null)
	{
		if ($connection !== NULL)
		{
			$this->connection = $connection;
		}

		if (is_null($this->connection))
		{
			$this->connection = LMongo::connection();
		}

		if (is_null(static::$collection))
		{
			static::$collection = strtolower(get_called_class());
		}
	}

	/**
	 * get Connection
	 *
	 * @return LMongo\Database
	 */
	private function getConnection(){
		return $this->connection;
	}

	/**
	 * get Collection
	 *
     * @param  string     $collection
	 * @return MongoCollection
	 */
	private function getCollection($collection){
		return $this->getConnection()->$collection;
	}



	/****************************************************
	 *	Magic Methods
	 ****************************************************/
	// @TODO: move magic methods to separate model
	/**
	 * Magic Method for handling dynamic method calls.
	 */
	public function __call($method, $parameters)
	{	
		if(method_exists($this->getCollection(static::$collection), $method))
		{
			return call_user_func_array(array($this->getCollection(static::$collection), $method), $parameters);
		}
		throw new \Exception("Method [$method] is not defined.");
	}

	/**
	 * Magic Method for handling dynamic static method calls.
	 */
	public static function __callStatic($method, $parameters)
	{
		$model = get_called_class();
		return call_user_func_array(array(new $model, $method), $parameters);
	}
}
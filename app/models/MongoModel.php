<?php

/**
 * MongoModel wrapper
 * @TODO: move to Mongo package
 */

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
	private function getCollection($collection = null){
		$collection = $collection?: static::$collection;
		return $this->getConnection()->$collection;
	}


	/**
	 * get All
	 *
	 * @return array
	 */
	private function all(){
		$results = $this->getCollection()->find();
		$collection = array();
		foreach ($results as $key => $value) {
			$collection[]=$value;
		}
		return $collection;
	}

	/****************************************************
	 *	Magic Methods
	 ****************************************************/

	/**
	 * Magic Method for handling dynamic method calls.
	 */
	public function __call($method, $parameters)
	{	
		if(method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $parameters);
		}

		if(method_exists($this->getCollection(), $method))
		{
			return call_user_func_array(array($this->getCollection(), $method), $parameters);
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
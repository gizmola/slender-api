<?php

/**
 * MongoModel wrapper
 * @TODO: move to Mongo package
 */
class MongoModel
{

	/**
	 *
	 * @var string
	 */
	protected $collectionName;
	
	/**
	 * Collection for active model
	 *
	 * @var LMongo\Query\Builder
	 */
	protected $collection;

	/**
	 * Connection to use for active model
	 *
	 * @var LMongo\Database
	 */
	protected $connection;

	/**
	 * Constructor
	 * 
	 * @param LMongo\Database $connection
	 */
	public function __construct($connection = null)
	{
		if ($connection !== null) {
			$this->connection = $connection;
		}

		if (is_null($this->connection)) {
			$this->connection = \App::make('MongoSiteSingleton');
		}

		if (is_null($this->collection)) {
			$class = array_slice(explode('\\', get_called_class()), -1);
			$this->collectionName = strtolower(array_shift($class));
		}
	}

	/**
	 * Get Connection
	 *
	 * @return LMongo\Database
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Get Collection
	 *
	 * @param  string     $collectionName
	 * @return LMongo\Query\Builder
	 */
	public function getCollection($collectionName = null)
	{
		$collectionName = $collectionName ?: $this->collectionName;
		return $this->connection->collection($this->collectionName);
	}


	/**
	 * Overwriting Execute the query function.
	 *
	 * @param  array  $columns
	 * @return Array
	 */
	public function get($columns = array())
	{
		$result = $this->getCollection()->get($columns);
		$return = array();
		foreach ($result as $value) 
		{
			$return[] = $value;
		}
		return $return;
	}

	/**
	 * Get record by ID
	 *
	 * @param  string  $id
	 * @return mixed 
	 */
	public function find($id)
	{
//		if(!$id instanceof MongoId){
//			$id = new MongoId($id);
//		}

		return $this->getCollection()->where('_id', $id)->first();
	}

	/**
	 * Magic Method for handling dynamic method calls.
	 */
	public function __call($method, $parameters)
	{
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $parameters);
		}

		if (method_exists($this->getCollection(), $method)) {
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
<?php

/**
 * MongoModel wrapper
 * @TODO: move to Mongo package
 */
class MongoModel
{

	/**
	 * Collection for active model
	 *
	 * @var null
	 */
	protected $collection = null;

	/**
	 * Connection to use for active model
	 *
	 * @var null
	 */
	protected $connection = null;

	public function __construct($connection = null)
	{
		if ($connection !== null) {
			$this->connection = $connection;
		}

		if (is_null($this->connection)) {
			$this->connection = \App::make('mongo')->connection($this->site);

		}

		if (is_null($this->collection)) {
			$this->collection = strtolower(get_called_class());
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
	 * @param  string     $collection
	 * @return MongoCollection
	 */
	public function getCollection($collection = null)
	{
		$collection = $collection? : $this->collection;
		return $this->connection->collection($this->collection);
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
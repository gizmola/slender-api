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
	public static $collection = null;

	/**
	 * Connection to use for active model
	 *
	 * @var null
	 */
	public $connection = null;

	public function __construct($connection = null)
	{
		if ($connection !== null) {
			$this->connection = $connection;
		}

		if (is_null($this->connection)) {
			$this->connection = LMongo::connection();
		}

		if (is_null(static::$collection)) {
			static::$collection = strtolower(get_called_class());
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
		$collection = $collection? : static::$collection;
		// if(method_exists($this->getConnection(), $collection)){
			return $this->getConnection()->$collection;
		// }else{
			// var_dump($this->getConnection(), $collection);
		// }
	}

	/**
	 * Get all
	 *
	 * @return array
	 */
	public function all()
	{
		$results = $this->getCollection()->find();
		$collection = array();
		foreach ($results as $key => $value) {
			$collection[] = $value;
		}
		return $collection;
	}
	
	public function insert(array $data)
	{
		return $this->getCollection()->insert($data);
	}
	
	public function update($id, array $data)
	{
		$entity = $this->getCollection()->findOne($id);
		echo "<pre>" . var_dump($entity) . "</pre>";
		die(__FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message");


		if ($entity){
			foreach ($data as $k => $v) {
				$entity->$k = $v;
			}
		}
		return $this->getCollection()->update($entity);
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
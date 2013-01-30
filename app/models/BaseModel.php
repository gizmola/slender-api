<?php

/**
 * Base Model
 */
class BaseModel extends MongoModel
{
	protected $site = 'default';

	protected $schema = array();
	
	protected $relations = array(
		'children' => array(),
		'parents' => array(),
	);

	public function findById($id)
	{
		return parent::find($id);
	}
	
	/**
	 * Get a collection of documents in this collection
	 * 
	 * @param array $conditions
	 * @param array $orders
	 * @param type $limit
	 * @param type $offset
	 */
	public function findMany(array $conditions, array $orders, $limit = null, $offset = null)
	{
	}
	
	public function insert(array $data)
	{	
	}
	
	public function update(array $data)
	{	
	}
	
	public function options()
	{	
	}
	
	/**
	 * @todo
	 * @param array $data
	 */
	protected function embedChildren(array $data)
	{
	}
	
	/**
	 * @todo
	 * @param array $data
	 * @param boolean $isDelete
	 */
	protected function updateParents(array $data, $isDelete = false)
	{
	}
}

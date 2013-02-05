<?php

use Dws\Slender\Api\Support\Query\FromArrayBuilder;

/**
 * Base Model
 */
class BaseModel extends MongoModel
{
	protected $site = 'default';

	protected $timestamp = false;

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
	public function findMany(array $conditions, array $fields, array $orders, $take = null, $skip = null)
	{
		
		$builder = $this->getCollection();
		$builder = FromArrayBuilder::buildWhere($builder, $conditions);

		if ($orders) {
			FromArrayBuilder::buildOrders($builder,$orders);
		}

		if ($take) {
			$builder = $builder->take($take);	
		}

		if ($skip) {
			$builder = $builder->skip($skip);	
		}

		if ($fields) {
			$result = $builder->get($fields);	
		} else {
			$result = $builder->get();	
		}

		$result = $builder->get();		

		$entities = array();
		foreach ($result as $entity) {
			$entities[] = $entity;
		}
		return $entities;
	}
	
	/**
	 * Insert data into the collection
	 * 
	 * @param array $data
	 * @return array
	 */
	public function insert(array $data)
	{
		if($this->timestamp){
			$data['created_at'] = new MongoDate();
			$data['updated_at'] = new MongoDate();
		}
		$id = $this->getCollection()->insert($data);
		$entity = $this->findById($id);
		$this->updateParents($id, $entity);
		return $entity;
	}
	
	/**
	 * Update data of the record
	 *
	 * @param string $id 
	 * @param array $data
	 * @return array
	 */
	public function update($id, array $data)
	{
		if($this->timestamp){
			$data['updated_at'] = new MongoDate();
		}
		$this->getCollection()->where('_id', $id)->update($data);
		$entity = $this->findById($id);
		return $entity;
	}
	
	/**
	 * Delete record
	 *
	 * @param string $id 
	 * @return array
	 */
	public function delete($id)
	{
		$this->getCollection()->where('_id', $id)->delete();
		$this->updateParents($id, true);
		return true;
	}
	
	/**
	 * Return information about abstract record including 
	 * representation of the fields to help to pass correct data
	 * for insert and update methods
	 *
	 * @return array
	 */
	public function options()
	{	
		// @TODO clean up $this->schema before passing up
		return array(
				'fields' => $this->schema,
			);
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
	protected function updateParents($id, $isDelete = false)
	{
	}


	public function getSchemaValidation(){
		return $this->schema;
	}
}

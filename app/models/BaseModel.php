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
		$result = $this->getCollection()->get();
		$entities = array();
		foreach ($result as $entity) {
			$entities[] = $entity;
		}
		return $entities;
	}
	
	public function insert(array $data)
	{
		$id = $this->getCollection()->insert($data);
		$entity = $this->findById($id);
		$this->updateParents($id, $entity);
		return $entity;
	}
	
	public function update($id, array $data)
	{
		$this->getCollection()->where('_id', $id)->update($data);
		$entity = $this->findById($id);
		return $entity;
	}
	
	public function delete($id)
	{
		$this->getCollection()->where('_id', $id)->delete();
		$this->updateParents($id, true);
		return true;
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
	protected function updateParents($id, $isDelete = false)
	{
	}
}

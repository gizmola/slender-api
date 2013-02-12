<?php

namespace Slender\API\Model;

use Dws\Slender\Api\Support\Util\UUID;
use Dws\Slender\Api\Support\Query\FromArrayBuilder;

/**
 * Base Model
 */
class BaseModel extends MongoModel
{
	protected $site = 'default';


	protected $timestamp = false;

	protected $schema = [];

	protected $resolver;
	
	protected $relations = [
		'children' => [],
		'parents' => [],
	];

	public function findById($id)
	{
		return parent::find($id);
	}
	
	/**
	 * Get a collection of documents in this collection
	 * 
	 * @param array $where
	 * @param array $orders
	 * @param type $limit
	 * @param type $offset
	 */
	public function findMany(array $where, array $fields, array $orders, &$meta, 
							array $aggregate = null, $take = null, $skip = null, $count = null, $with = null)
	{
	
		$builder = $this->getCollection();

		if ($where) {
			$builder = FromArrayBuilder::buildWhere($builder, $where);
		}

		if ($aggregate) {

			/*
			determine the type of aggregate function
			end run the correct execution
			return the aggregate data via ref $meta
			*/

			if ($aggregate[0] == 'count') {
				$results = $builder->count();
			} else {
				$results = $builder->$aggregate[0]($aggregate[1]); 
				$meta['count'] = null;
			}


			$meta[$aggregate[0]] = $results; 

			return [];
		}

		if ($orders) {
			$builder = FromArrayBuilder::buildOrders($builder,$orders);
		}

		$meta['count'] = $builder->count();

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

		$entities = [];

		foreach ($result as $entity) {
			$entities[] = $entity;
		}

		if ($with) {
			$this->embedWith($with, $entities);
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
			$data['created_at'] = new \MongoDate();
			$data['updated_at'] = new \MongoDate();
		}

		if(!isset($data['_id'])){
			$data['_id'] = UUID::v4();	
		}
		
		//embed child data
		$embeddedRelations = $this->getEmbeddedRelations();


		foreach ($embeddedRelations as $relation) {

			$childIntsance = $this->createRelatedClass($relation);
			$this->embedChildData($data[$relation],$childIntsance);
	
		}
				
		$id = $this->getCollection()->insert($data);
		$entity = $this->findById($id);
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

		if (!$this->updateParents($entity)) {
			throw new \Exception("Error updating parent data");
		}

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

	public function getName()
	{
		return $this->resolver->parseClassName(get_class($this));
	}

	public function getNameSpace()
	{
		return $this->resolver->parseNameSpace(get_class($this));
	}

	public function getSchemaValidation()
	{
		return $this->schema;
	}

	public function addRelations($type, $relations)
	{
		foreach ($relations as $k => $v) {
			$this->relations[$type][$k] = $v;	
		}
	}

	public function getRelations()
	{
		return $this->relations;
	}

	public function getEmbeddedRelations($reverse=true)
	{
		$embedded = [];

		foreach ($this->relations['children'] as $k => $v) {
			if ($v && $reverse) {
				$embedded[] = $k; 	
			} elseif (!$v && !$reverse) {
				$embedded[] = $k;
			}
		}

		return $embedded;
	}

	/**
	 * Replaces a child ids with an embeded objects
	 * in the passed array
	 * @param array $childIds
	 * @param ChildClassInstance $childIntsance
	 * @return void
	 */
	public function embedChildData(&$childIds,$childIntsance)
	{

		for ($i = 0; $i < count($childIds); $i++) {

			$child = $childIntsance->findById($childIds[$i]);	
			
			if ($child) {
				$childIds[$i] = $child;	
			}

		}
	}

	protected function embedWith($with,&$entities)
    {  
		$emdbedded = [];
		foreach ($with as $array) {
			$emdbedded[$array[0]] = $array[1];	
		}  

		if ($emdbedded['children']) {
			
			$notEmbeddedRelations = $this->getEmbeddedRelations(false);

			foreach ($notEmbeddedRelations as $relation) {
				
				$childIntsance = $this->createRelatedClass($relation);
				
				for ($i = 0; $i < count($entities); $i++) {
					$this->embedChildData($entities[$i][$relation],$childIntsance);
				}
				
			}

		}
	}

	public function isEmbedded($child) {
		return in_array($child, $this->getEmbeddedRelations());
	}

	private function createRelatedClass($name)
	{
		$namespacedName = "\\" . $this->getNameSpace() . "\\" .  ucfirst($name);
		$newClass = $this->resolver->create($namespacedName, $this->getConnection());
		$newClass->setResolver($this->resolver);
		return $newClass;
	}

	public function updateParents($entity)
	{

		try{

			$myLcName = strtolower($this->getName());

			$parents = $this->getRelations()['parents'];

			foreach ($parents as $p => $c) {
				
				$parentClassName =  ucfirst($p);
				$parentClass = $this->createRelatedClass(ucfirst($p));
				
				if (in_array($myLcName, $parentClass->getEmbeddedRelations())) {
					
					$results = $parentClass->getCollection()->where("{$myLcName}._id",$entity['_id'])->get();

					foreach ($results as $res) {
						$this->updateParentData($entity, $res[$myLcName]);
						$parentId = $this->shiftId($res);
						$parentClass->getCollection()->where('_id', $parentId)->update($res);
					}

				}

			}

		} catch (\Exception $e) {
			return false;
		}

		return true;

	}

	public function updateParentData($childData, &$children, $isDelete=false)
	{

		$index = null;

		for ($i=0; $i < count($children); $i++) {
			
			if ($childData['_id'] == $children[$i]["_id"]) {
				$index = $i;
				break;
			}

		}

		if ($index !== null) {
			if ($isDelete) {
				unset($children[$i]);	
			} else {
				$children[$i] = $childData;
			}
		}

		return $index;
	
	}

	public function getResolver()
	{
		return $this->resolver;
	}

	public function setResolver($resolver)
	{
		$this->resolver = $resolver;
	}

	public function shiftId(&$data)
	{
		$id = $data['_id'];
		unset($data['_id']);
		return $id;
	}
	
}

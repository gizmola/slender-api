<?php

namespace Slender\API\Model;

use \Validator;
use Dws\Slender\Api\Support\Query\FromArrayBuilder;
use Dws\Utils\Arrays as ArrayUtil;
use Dws\Utils\UUID;
use Illuminate\Support\MessageBag;
use LMongo\Database as Connection;

/**
 * Base Model
 */
class BaseModel extends MongoModel
{
    /**
     *
     * @var string|null
     */
    protected $site = null;

    protected $timestamp = false;

    /**
     * @var array
     */
    protected $schema = [];

    /**
     * @var array
     */
    protected $extendedSchema = [];

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * Failed validation messages
     *
     * @var MessageBag
     */
    protected $validationMessages;

    /**
     * Constructor
     *
     * @param \LMongo\Database $connection
     */
    public function __construct(Connection $connection = null)
    {
        parent::__construct($connection);
        /*
        * extending classes can define only new attributes
        * not defined in the base class by providing an
        * $extendedSchema attribute
        */
        $this->setSchema(array_merge($this->schema, $this->extendedSchema));
    }

    /**
     * Find a single item
     *
     * @param string $id
     * @param boolean $no_cache
     * @return array
     */
    public function findById($id, $no_cache = false)
    {
        if (!\Config::get('cache.enabled') OR \Input::get('no_cache') OR $no_cache) {

            return parent::find($id);

        } else {

            return \Cache::remember($this->collectionName . "_" . $id, \Config::get('cache.cache_time'), function() use($id){return parent::find($id);});

        }
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
                             array $aggregate = null, $take = null, $skip = null, $with = null)
    {
        if (!\Config::get('cache.enabled') OR \Input::get('no_cache')) {
            return $this->findManyQuery($where, $fields, $orders, $meta, $aggregate, $take, $skip, $with);
        } else {
            /*
            * To distiunqish between ?foo=bar&a=b and ?a=b&foo=bar(same query)
            * we get the params as array, remove unused params, sort it and make it into a string again
            */
            $query = \Input::all();
            unset($query['purge_cache']);
            unset($query['no_cache']);
            asort($query);
            $query = \Request::path() . http_build_query($query);

            if(\Input::get('purge_cache')) {
                \Cache::forget($query);
            }
            //@TODO: Line below is not pretty
            return \Cache::remember($query, \Config::get('cache.cache_time'), function() use ($where, $fields, $orders, $meta, $aggregate, $take, $skip, $with){ return $this->findManyQuery($where, $fields, $orders, $meta, $aggregate, $take, $skip, $with);});
        }

    }

    /**
     *
     * @param array $where
     * @param array $fields
     * @param array $orders
     * @param type $meta
     * @param array $aggregate
     * @param type $take
     * @param type $skip
     * @param type $with
     * @return type
     */
    protected function findManyQuery(array $where, array $fields, array $orders, &$meta,
                             array $aggregate = null, $take = null, $skip = null, $with = null)
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

        if ($this->timestamp) {
            $data['created_at'] = new \MongoDate();
            $data['updated_at'] = new \MongoDate();
        }

        $childrenSent = array_key_exists('_children', $data) ? $data['_children'] : null;
        $parentsSent = array_key_exists('_parents', $data) ? $data['_parents'] : null;
        unset($data['_children']);
        unset($data['_parents']);

        $data['_id'] = UUID::v4();
        $this->embedChildren($data);
        $id = $this->getCollection()->insert($data);
        $entity = $this->findById($id);

        if (\Config::get('cache.enabled')) {
            \Cache::put($this->collectionName . "_" . $id, $entity, \Config::get('cache.cache_time'));
        }

        //embed existing children in this entity
        if ($childrenSent) {
            $entity = $this->embedNewChildrenInEntity($entity, $childrenSent);
        }

        //embed this entity to existing parents
        if ($parentsSent) {
            $this->embedEntityInNewParents($parentsSent, $entity);
        }

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

        //collect parents or children to be embedded in, or embed, respectively
        $childrenSent = array_key_exists('_children', $data) ? $data['_children'] : null;
        $parentsSent = array_key_exists('_parents', $data) ? $data['_parents'] : null;
        unset($data['_children']);
        unset($data['_parents']);

        if($this->timestamp){
            $data['updated_at'] = new \MongoDate();
        }

        //first save any non-related data
        $this->getCollection()->where('_id', $id)->update($data);
        $entity = $this->findById($id, true);

        //embed existing children in this entity
        if ($childrenSent) {
            $entity = $this->embedNewChildrenInEntity($entity, $childrenSent);
        }

        //cache the entity
        if (\Config::get('cache.enabled'))
            \Cache::put($this->collectionName . "_" . $id, $entity, \Config::get('cache.cache_time'));

        //update current associated parents
        if (!$this->updateParents($entity)) {
            throw new \Exception("Error updating parent data");
        }

        //embed this entity to existing parents
        if ($parentsSent) {
            $this->embedEntityInNewParents($parentsSent, $entity);
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
        $this->updateParents($id);
        if (\Config::get('cache.enabled'))
            \Cache::forget($this->collectionName . "_" . $id);
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
     * Get the schema
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Set the schema
     *
     * @param array $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Get the schema
     *
     * @deprecated use getSchema()
     * @return type
     */
    public function getSchemaValidation()
    {
        return $this->getSchema();
    }
    /**
     * Embedd all the embeddable children
     * of a parent class
     * @param array $data
     * @return void
     */
    private function embedChildren(&$data)
    {

        //embed child data
        $embeddedRelations = $this->getEmbeddedRelations();

        foreach ($embeddedRelations as $resource => $config) {

            if (array_key_exists($config['embedKey'], $data)) {
                $childIntsance = $this->createRelatedClass($resource, $config);
                $this->embedChildData($data[$config['embedKey']], $childIntsance);
            }      

        }

    }

    /**
     *
     * @param type $with
     * @param type $entities
     */
    protected function embedWith($with,&$entities)
    {
        $emdbedded = [];
        
        foreach ($with as $array) {
            $emdbedded[$array[0]] = $array[1];
        }

        if ($emdbedded['children']) {

            $notEmbeddedRelations = $this->getEmbeddedRelations(false);

            foreach ($notEmbeddedRelations as $resource => $config) {

                $childIntsance = $this->createRelatedClass($resource, $config);

                for ($i = 0; $i < count($entities); $i++) {
                    $this->embedChildData($entities[$i][$config['embedKey']], $childIntsance);
                }

            }

        }
    }

    /**
     *
     * @param type $resource
     * @param type $config
     * @return \Slender\API\Model\class
     */
    private function createRelatedClass($resource, $config)
    {
        $resolver = \App::make('resource-resolver');
        $class = '\\' . $config['class'];
        $class = new $class($this->getConnection());
        $class->setRelations($resolver->buildModelRelations($resource, $this->site));
        $class->setSite($this->site);
        return $class;
    }

    /**
     *
     * @param type $entity
     * @param type $isDelete
     * @return boolean
     */
    public function updateParents($entity, $isDelete=false)
    {

        try{

            $relations = $this->getRelations();
            if (!$relations || !is_array($relations) || !array_key_exists('parents', $relations)) {
                return true;
            }

            $parents = $relations['parents'];

            foreach ($parents as $resource => $config) {

                $parentClass = $this->createRelatedClass($resource, $config);
                $embeded = $parentClass->getEmbeddedRelations();
                $classConfig = $parentClass->getChildByClassName(get_class($this), $embeded);

                if ($classConfig) {

                    $embedKey = $classConfig['embedKey'];
                    $results = $parentClass->getCollection()->where("{$embedKey}._id",$entity['_id'])->get();

                    foreach ($results as $res) {
                        
                        /*
                        * check if one-one (assoc) 
                        * or one-many (indexed)
                        */
                        if (!ArrayUtil::isAssociative($res[$embedKey])) {

                            $this->updateParentData($entity, $res[$embedKey], $isDelete);
                            
                        } else {

                            if ($isDelete) {
                                $res[$embedKey] = null;    
                            } else {
                                $res[$embedKey] = $entity;
                            }

                        }

                        /*
                        * a call to the parent's update method should
                        * recursively update grandparents if they exist
                        */
                        $parentId = ArrayUtil::shiftId($res);
                        $parentClass->update($parentId, $res);
                        \Cache::put($this->collectionName . "_" . $parentId, $res, \Config::get('cache.cache_time'));

                    }

                }

            }
        } catch (\Exception $e) {
            return false;
        }

        return true;

    }

    /**
     *
     * @param type $childData
     * @param type $children
     * @param type $isDelete
     * @return int
     */
    public function updateParentData($childData, &$children, $isDelete=false)
    {

        $index = null;

        //determine which of the children is the match
        for ($i=0; $i < count($children); $i++) {
            if ($childData['_id'] == $children[$i]["_id"]) {
                $index = $i;
                break;
            }
        }

        //delete or update the child
        if ($index !== null) {
            if ($isDelete) {
                unset($children[$i]);
            } else {
                $children[$i] = $childData;
            }
        }

        return $index;
    }

    public function addRelations($type, $relations)
    {
        if (!isset($this->relations[$type])) {
            $this->relations[$type] = [];
        }

        $this->relations[$type] = array_merge($this->relations[$type],$relations);
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function setRelations($relations)
    {
        $this->relations = $relations;
        return $this;
    }

    public function getEmbeddedRelations($natural=true)
    {
        $embedded = [];

        if (isset($this->relations['children']) && is_array($this->relations['children'])) {
            foreach ($this->relations['children'] as $k => $v) {
                if (($v['embed'] && $natural) || (!$v['embed'] && !$natural)) {
                    $embedded[$k] = $v;
                }
            }
        }

        return $embedded;
    }



    public function getSite()
    {
        return $this->site;
    }

    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    public function getChildByClassName($name,$relations = null)
    {

        if (!$relations) {
            $relations = $this->relations['children'];
        }

        foreach ($relations as $k => $v) {
            if ($v['class'] == $name) {
                return $v;
            }
        }

        return false;
    }

    /**
     * Filters the schema's validation by the keys of the input.
     * Useful for partial updates.
     *
     * @param array $input
     * @param boolean $isPartial
     * @return \Validator
     * @throws \Exception
     */
    protected function makeCustomValidator($input, $isPartial)
    {
        $validationInfo = [];
        $schema = $this->getSchema();
        $keys = array_keys($schema);
        if ($isPartial) {
            $keys = array_intersect($keys, array_keys($input));
        }
        array_map(function($k) use ($schema, $input, &$validationInfo){
            $validationInfo[$k] = $schema[$k];
        }, $keys);
        if (empty($validationInfo)) {
            throw new \Exception("No valid parameters sent");
        }

        return Validator::make($input, $validationInfo);
    }

    /**
     * Is the given data valid for this model?
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data, $isPartial = false)
    {
        $validator = $this->makeCustomValidator($data, $isPartial);
        if ($validator->fails()) {
            $this->validationMessages = $validator->messages();
            return false;
        }
        return true;
    }

    public function getValidationMessages()
    {
        return $this->validationMessages;
    }

    public function setValidationMessages(MessageBag $validationMessages)
    {
        $this->validationMessages = $validationMessages;
        return $this;
    }

    public function clearValidationMessages()
    {
        return $this->setValidationMessages(null);
    }

    public function getParentRelations()
    {
        
        if (!$this->relations || !is_array($this->relations) || !array_key_exists('parents', $this->relations)) {
            return [];
        }

        return $this->relations['parents'];
    }

    public function getChildRelations()
    {
        
        if (!$this->relations || !is_array($this->relations) || !array_key_exists('children', $this->relations)) {
            return [];
        }

        return $this->relations['children'];
    }

    /**
     * Replaces a child ids with an embeded objects
     * in the passed array
     * @param array $childIds
     * @param ChildClassInstance $childIntsance
     * @return void
     */
    public function embedChildData(&$childIds, $childIntsance)
    {

        /*
        * if the relation is 1-1
        * set the data to the child
        * data
        */
        if (!is_array($childIds)) {

            $child = $childIntsance->findById($childIds);

            if ($child) {
                $childIds = $child;
            }

            return;

        }

        for ($i = 0; $i < count($childIds); $i++) {

            /*
            * to allow for adding new children
            * to an existing parent, we check that
            * the data type is not already and array
            * which would signify an embeded object(s)
            */
            if (!is_array($childIds[$i])) {

                $child = $childIntsance->findById($childIds[$i]);

                if ($child) {
                    $childIds[$i] = $child;
                }

            }

        }

    }

    public function addNewChildIds(&$entity, $childList)
    {

        foreach ($childList as $k=> $v) {

            if (!isset($entity[$k])) {
                continue;
            }

            if (!is_array($entity[$k]) && is_array($v)) {
                throw new \Exception("Entity attribute $k cannot be set to array");
            }

            if (!is_array($entity[$k])) {
                $entity[$k] = $v;
            } else {
                $entity[$k] = array_merge($entity[$k], $v);    
            }           

        }

    }

    public function embedNewChildrenInEntity($entity, $children)
    {
        $this->addNewChildIds($entity, $children);
        $this->embedChildren($entity);
        $parentId = ArrayUtil::shiftId($entity);
        return $this->update($parentId, $entity);
    }

    public function embedEntityInNewParents($parentList, $childData)
    {

        //get all the parents of the current child
        $parentsRelations = $this->getParentRelations();

        //check all resources before proceeding
        foreach ($parentList as $k => $v) {
            if (!array_key_exists($k, $parentsRelations)) {
                throw new \Exception('Attempting to add child of class ' . get_class($this) . ' to non-existant parent resource ' . $k);
            }            
        }

        /*
        * loop through all the parent keys
        * get the list of ids, and instantiante each correct parent
        * append the child ids or set the child data
        * run embed children if needed.
        */
        foreach ($parentList as $resource => $ids) {

            //get the parent config from the relations
            $parentConfig = $parentsRelations[$resource];
            //get the generic parent class (basically a factory)
            $parentClass = $this->createRelatedClass($resource, $parentConfig);
            //get the child config from the parent for embed key and whether to embed
            $childConfigFromParent = $parentClass->getChildByClassName(get_class($this));
            $childEmbedKey = $childConfigFromParent["embedKey"];
            $toEmbded = $childConfigFromParent['embed'];

            /*
            * instatntiate each individual parent
            */

            foreach($ids as $parentId) {
                
                //retrive the parent data from db
                $parentEntity = $parentClass->findById($parentId, true);

                //embed or add ids

                /*
                * @TODO: won't be able to tell the difference between empty arrays
                * so assoc and indexed will look the same
                */
                if (is_array($parentEntity[$childEmbedKey]) && !ArrayUtil::isAssociative($parentEntity[$childEmbedKey])) {

                    
                    // one-many relation
                    $childId = $childData['_id'];
                    $parentClass->addNewChildIds($parentEntity, [$childEmbedKey => [$childId]]);

                    // embedded one-many
                    if ($toEmbded) {
                        $parentClass->embedChildren($parentEntity);   
                    }

                } elseif (!$toEmbded) {

                    // non-embedded one-one
                    $parentEntity[$childEmbedKey] = $childData['_id'];
                
                } else {
                
                    // embedded one-one
                    $parentEntity[$childEmbedKey] = $childData;   
                
                }

            }

            //update the parent's data
            $parentId = ArrayUtil::shiftId($parentEntity);
            $newParentData = $parentClass->update($parentId, $parentEntity);

        }

    }

}

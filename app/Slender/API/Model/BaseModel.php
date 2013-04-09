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

    protected $resolver;

    /**
     * The client user making the request
     *
     * @var array
     */
    protected $clientUser;

    /**
     * Constructor
     *
     * @param \LMongo\Database $connection
     */

    const UPDATE_METHOD_DELETE = true;

    public function __construct(Connection $connection = null)
    {
        parent::__construct($connection);
        /*
        * extending classes can define only new attributes
        * not defined in the base class by providing an
        * $extendedSchema attribute
        */
        $this->setSchema(array_merge($this->schema, $this->extendedSchema));
        $this->resolver = \App::make('resource-resolver');
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

        if ($childrenSent) {
            $this->embedChildEntities($data, $childrenSent);
        }

        $id = $this->getCollection()->insert($data);
        $entity = $this->findById($id);

        if (\Config::get('cache.enabled')) {
            \Cache::put($this->collectionName . "_" . $id, $entity, \Config::get('cache.cache_time'));
        }

        //embed this entity to existing parents
        if ($parentsSent) {
            $this->addToParentEntities($entity, $parentsSent);
        }

        $this->audit($entity);

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

        $before = $this->getCollection()->where('_id', $id)->first();

        //first save any non-related data
        $this->getCollection()->where('_id', $id)->update($data);
        $entity = $this->findById($id, true);

        //embed existing children in this entity
        if ($childrenSent) {
            $this->embedChildEntities($entity, $childrenSent);
            $entity = ArrayUtil::except($entity,'_id');
            $this->getCollection()->where('_id', $id)->update($entity);
            $entity = $this->findById($id, true);
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
            $this->addToParentEntities($entity, $parentsSent);
        }

        $this->audit($entity, $before);

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
        $this->updateParents(["_id" => $id], self::UPDATE_METHOD_DELETE);
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
     *
     * @param type $with
     * @param type $entities
     */
    protected function embedWith($with, &$entities)
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

                    $childList = array_map(function($x)
                    {
                        return $x['_id'];
                    }, 
                    $entities[$i][$config['embedKey']]);

                    $this->embedChildEntities($entities[$i], [$config['embedKey'] => $childList]);

                }

            }

        }
    }
    /**
    * add the current entity to a list of existing
    * parent entities
    * @param array $childEntity the current entitie's data
    * @param array $parentList a list of parent keys => list of ids
    */
    public function addToParentEntities($childEntity, $parentList)
    {
        $parents = $this->getParentRelations();
        

        foreach ($parentList as $resource => $ids) {
            
            $config = $parents[$resource];
            $factory = $this->createRelatedClass($resource, $config);
            $parentConfig = $this->getChildByClassName(get_class($this), $factory->getChildRelations());
            $embed = $parentConfig['embed']; 
            $relationType = $parentConfig['type'];
            $embedKey = $parentConfig['embedKey'];
            $parentEntities = $this->getEntities($factory, $ids);
            $embedData = ($embed) ? $childEntity : $childEntity['_id'];

            foreach ($parentEntities as $PEntity) {
                $this->embeddedChildData($PEntity, $embedKey, $embedData, $relationType);
                $PEntityId = ArrayUtil::shiftId($PEntity);
                $factory->update($PEntityId, $PEntity);
            }

        }
    }
    /**
    * add child entities to the current entity
    * @param array $parentEntity the current entitie's data
    * @param array $childList a list of child keys => list of ids
    */
    public function embedChildEntities(&$parentEntity, $childList)
    {

        $children = $this->getChildRelations();

        foreach ($childList as $resource => $ids) {
            
            if (!array_key_exists($resource, $children)) {
                continue;
            }

            $config = $children[$resource];
            $embed = $config['embed'];
            $relationType = $config['type'];

            if (!$embed) {

                $embedData = $ids;
            
            } else {

                $factory = $this->createRelatedClass($resource, $config);
                $embedData = $this->getEntities($factory, $ids);

            }

            foreach ($embedData as $data) {

                $this->embeddedChildData($parentEntity, $resource, $data, $relationType);   

            }

        }
 
    }
    /**
    * get a collections of entity objects
    * @param classInstance $factory finds each entity in db
    * @param array $idlist entity ids
    * @return array
    */
    public function getEntities($factory, $idlist)
    {
        
        $entities = [];
        
        foreach ($idlist as $id) {
            $entity = $factory->findById($id);
            if ($entity) {
                $entities[] = $entity;
            }  
        }

        return $entities;

    }
    /**
    * embed child data into a parent entity at a particular key
    * @param array $parentEntity the current entitie's data
    * @param string $embedKey the key at which to embed data
    * @param mixed $childData the data to embed
    * @param string $relationType (has-one, has-many)
    * @return array
    */
    public function embeddedChildData(&$parentEntity, $embedKey, $childData, $relationType)
    {
        $defaultValue = ($relationType == 'has-many') ? [] : null;
        ArrayUtil::updateKey($parentEntity, $embedKey, $childData, $defaultValue, true);
    }

    /**
     *
     * @param type $entity
     * @param type $isDelete
     * @return boolean
     */
    public function updateParents($entity, $isDelete=false)
    {

        $parents = $this->getParentRelations();

        foreach ($parents as $resource => $config) {

            $factory = $this->createRelatedClass($resource, $config);
            $childRelations = $factory->getChildRelations();
            $childConfig = $factory->getChildByClassName(get_class($this), $childRelations);
            $embed = $childConfig['embed'];
            $relationType = $childConfig['type'];
            $embedKey = $childConfig['embedKey'];

            /*
            * we stop here if this entity is not
            * embedded, unless we are deleting
            */
            if (!$embed && !$isDelete) {
                continue;
            }

            //get all the parents containing the current entity
            $parentEntities = $factory->getCollection()->where("{$embedKey}._id",$entity['_id'])->get();

            foreach ($parentEntities as $PEntity) {

                // update the parent data
                $this->updateParentData($entity, $PEntity[$embedKey], $relationType, $isDelete);
                /*
                * a call to the parent's update method should
                * recursively update grandparents if they exist
                */
                $parentId = ArrayUtil::shiftId($PEntity);
                $newParent = $factory->update($parentId, $PEntity);
                //update the parent's cache
                \Cache::put($this->collectionName . "_" . $parentId, $PEntity, \Config::get('cache.cache_time'));

            }

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
    public function updateParentData($childData, &$children, $relationType, $isDelete=false)
    {

        if ($relationType == 'has-one') {

            if ($isDelete) {
                $children = null;    
            } else {
                $children = $childData;
            }

        } else {

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
                    //reset the array keys
                    $children = array_values($children);
                } else {
                    $children[$i] = $childData;
                }
            }

        }
    
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

    public function getRelations()
    {
        return $this->relations;
    }

    public function setRelations($relations)
    {
        $this->relations = $relations;
        return $this;
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

    public function addRelations($type, $relations)
    {
        if (!isset($this->relations[$type])) {
            $this->relations[$type] = [];
        }

        $this->relations[$type] = array_merge($this->relations[$type],$relations);
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

    public function getResolver()
    {
        return $this->resolver;
    }

    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
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
     *
     * @param type $resource
     * @param type $config
     * @return \Slender\API\Model\class
     */
    public function createRelatedClass($resource, $config)
    {
        //hacky way to work in unit tests
        //@todo: fix
        $relations = $this->getResolver()->buildModelRelations($resource, $this->site);
        if (is_object($relations)) {
            return  $relations;  
        }

        $class = '\\' . $config['class'];
        $class = new $class($this->getConnection());
        $class->setRelations($relations);
        $class->setSite($this->site);
        return $class;

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

    /**
     * Get the client-user making the request
     *
     * @return array
     */
    public function getClientUser()
    {
        if (null === $this->clientUser) {
            try {
                $this->clientUser = \App::make('client-user');
            } catch (\Exception $e) {
            }
        }
        return $this->clientUser;
    }


    private function audit($after, $before = null){
        $audit = new Audit;
        return $audit->insert([
            'type'      => get_class($this),
            'before'    => $before,
            'after'     => $after,
        ]);
    }

}

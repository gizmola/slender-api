<?php

namespace Slender\API\Model;

use \Validator;
use Dws\Slender\Api\Support\Query\FromArrayBuilder;
use Dws\Utils\Arrays as ArrayUtil;
use Dws\Utils\UUID;
use Illuminate\Support\MessageBag;
use LMongo\Database as Connection;
use Dws\Slender\Api\Cache\CacheService;

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
    protected $cacheService;

    /**
     * Constructor
     *
     * @param \LMongo\Database $connection
     */

    const UPDATE_METHOD_DELETE = true;

    public function __construct(Connection $connection = null, CacheService $cacheService = null)
    {
        parent::__construct($connection);
        /*
        * extending classes can define only new attributes
        * not defined in the base class by providing an
        * $extendedSchema attribute
        */
        $this->setSchema(array_merge($this->schema, $this->extendedSchema));
        $this->resolver = \App::make('resource-resolver');
        $this->cacheService = $cacheService;

        /**
        * create an auditor
        */
        if (!(get_class($this) == 'Slender\API\Model\Audit'))
            $this->auditor = new Audit;

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
        
        $cache = $this->getCacheService();

        if (empty($cache) || !$cache->enabled() || $no_cache) {

            return parent::find($id);

        } else {

            $rememberBy = $this->collectionName . "_" . $id;
            $callback = function() use ($id) {return parent::find($id);};
            return $cache->getData($rememberBy, $callback);

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
    public function findMany($queryTranslator)
    {

        $cache = $this->getCacheService();


        if (empty($cache) || !$cache->enabled()) {

            return $this->findManyQuery($queryTranslator);
        
        } else {

            $rememberBy = $cache->buildRememberByFindMany();
            $callback = function() use ($queryTranslator) 
            { 
                return $this->findManyQuery($queryTranslator);
            };
            return $cache->getData($rememberBy, $callback);

        }

    }
    /**
     *
     * @param array $queryTranslator
     */
    protected function findManyQuery($queryTranslator)
    {

        $builder = $this->getCollection();
        $queryTranslator->setBuilder($builder);
        $result = $queryTranslator->translate();
        $meta = $queryTranslator->getMeta();
        $entities = [];

        foreach ($result as $entity) {
            $entities[] = $entity;
        }

        if ($queryTranslator->getParam('with')) {
            $this->embedWith($queryTranslator->getParam('with'), $entities);
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

        $cache = $this->getCacheService();
        
        if (!empty($cache) && $cache->enabled()) {
            $rememberBy = $this->collectionName . "_" . $id;
            $cache->putData($rememberBy, $entity);
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
        $cache = $this->getCacheService();

        if (!empty($cache) && $cache->enabled()) {
            $rememberBy = $this->collectionName . "_" . $id;
            $cache->putData($rememberBy, $entity);
        }

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
        $cache = $this->getCacheService();

        if (!empty($cache) && $cache->enabled())
            $cache->forget($this->collectionName . "_" . $id);
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
     * @param array $data
     * @param boolean $isPartial
     * @return \Validator
     * @throws \Exception
     */
    protected function makeCustomValidator($data, $isPartial)
    {
        $validationRules = [];

        $schema = $this->getSchema();

        /**
        *   Keep only Laravel validation rules from schema (not all additional info)
        *   in case of partial insert/update use rules only for fields passed for processing
        */
        foreach ($schema as $key => $value) {
            if(isset($data[$key]) || !$isPartial){
                foreach ($value as $id => $rule) {
                    if(is_numeric($id)){
                        $validationRules[$key][] = $rule;
                    }
                }
            }
        }
        return Validator::make($data, $validationRules);
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
        
        $audit = $this->getAuditor();

        return $audit->insert([
            'type'      => get_class($this),
            'before'    => $before,
            'after'     => $after,
        ]);

    }

    public function getCacheService()
    {
        return $this->cacheService;
    }

    public function setCacheService($cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
    * run an array modifying on an embedded
    * array of the document(s) matching the
    * search criteria
    *
    * @param string $fxn the function to run
    * @param array $wheres the document matching criteria
    * @param array $data
    */
    public function doArrayModifier($fxn, $wheres, $data, $multiple = true)
    {
        $update = array($fxn => $data);
        $builder = $this->getCollection();
        $collection = $this->getCollectionName();
        $database = $builder->getConnection();

        foreach ($wheres as $k => $v) {

            $builder->where($k, $v);
        
        }

        $criteria = $builder->compileWheres($builder);
        $result = $database->$collection->update($criteria, $update, ['multiple' => $multiple]);

        if(1 == (int) $result['ok'])
        {
            return $result['n'];
        }

        return 0;
    }

    /**
     * add an item to an embedded array
     * if it doesn't already exists
     *
     * @param  array  $wheres the document search criteria
     * @param array $data to push
     * @return int
     */
    public function addToSet($wheres, array $data, $multiple = true)
    {

        return $this->doArrayModifier('$addToSet', $wheres, $data, $multiple);
    }

    /**
    * remove an item from an embedded array
    *
    * @param array $wheres the document search criteria
    * @param array $data criteria for removal
    */
    public function pull($wheres, $data, $multiple = true)
    {
    
        return $this->doArrayModifier('$pull', $wheres, $data, $multiple); 
    
    }

    /**
    * update a document or insert it if it doesn't exist
    *
    * @param
    */
    public function upsert($wheres, $data, $multiple = true)
    {
      
        /*
        * get our query builder for searching
        */
        $builder = $this->getCollection();

        /*
        * set the search criteria
        */
        foreach ($wheres as $k => $v) {

            $builder->where($k, $v);
        
        }
        
        /*
        * if there are any counters in the update portion
        * pull them out and store them
        */
        $inc = ArrayUtil::shiftByKey($data, '$inc');

        /*
        * search for the existing doc
        */
        $entity = $builder->first();
        

        /*
        * if no doc exists, set the counters initial values
        * and save the new document
        *
        * else, increment each of the counters, then update
        * any remaining data
        */
        if (!$entity) {

            //set the initials counts
            if ($inc) {

                foreach ($inc as $k => $v)
                    $wheres[$k] = $v;

            }

            //save
            $entity = $this->insert($wheres);

        } else {

            //remove and store the enities uid
            $_id = ArrayUtil::shiftId($entity);

            //increment each counter
            if ($inc) {

                foreach ($inc as $k => $v)
                    $builder->increment($k, $v);

            }

            /*
            * if there is any data left, update
            * else, get the entity with the updated counters
            */
            if ($data) {

                $entity = $this->update($_id, $data);

            } else {

                $entity = $this->findById($_id, true);

            }

        }

        return $entity;
    
    }

    /**
    * get the auditor class
    *
    * @return Slender\API\Model\Audit
    */
    public function getAuditor()
    {
        return $this->auditor;
    }

    /**
    * manually reset the auditor
    * 
    * @param Slender\API\Model\Audit $auditor
    * @return Slender\API\Model
    */
    public function setAuditor($auditor)
    {
        $this->auditor = $auditor;
    }

}

<?php

namespace Slender\API\Model;

use \App;
use \Validator;
use Dws\Slender\Api\Resolver\ResourceResolver;
use Dws\Slender\Api\Support\Query\FromArrayBuilder;
use Dws\Slender\Api\Support\Util\Arrays as ArrayUtil;
use Dws\Slender\Api\Support\Util\UUID;
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
     * @var ResourceResolver
     */
    protected $resolver;

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

        $data['_id'] = UUID::v4();

        //embed child data
        $embeddedRelations = $this->getEmbeddedRelations();

        foreach ($embeddedRelations as $resource => $config) {
            $childIntsance = $this->createRelatedClass($resource, $config);
            $this->embedChildData($data[$config['embedKey']], $childIntsance);
        }

        $id = $this->getCollection()->insert($data);
        $entity = $this->findById($id);
        if (\Config::get('cache.enabled'))
            \Cache::put($this->collectionName . "_" . $id, $entity, \Config::get('cache.cache_time'));
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
            $data['updated_at'] = new \MongoDate();
        }

        $this->getCollection()->where('_id', $id)->update($data);
        $entity = $this->findById($id, true);

        if (\Config::get('cache.enabled'))
            \Cache::put($this->collectionName . "_" . $id, $entity, \Config::get('cache.cache_time'));

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
     * Replaces a child ids with an embeded objects
     * in the passed array
     * 
     * @param array $childIds
     * @param ChildClassInstance $childInstance
     * @return void
     */
    public function embedChildData(&$childIds, $childInstance)
    {
        for ($i = 0; $i < count($childIds); $i++) {
            $child = $childInstance->findById($childIds[$i]);
            if ($child) {
                $childIds[$i] = $child;
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
                $embedded = $parentClass->getEmbeddedRelations();

                $classConfig = $parentClass->getChildByClassName(get_class($this), $embedded);
                if ($classConfig) {

                    $embedKey = $classConfig['embedKey'];
                    $results = $parentClass->getCollection()->where("{$embedKey}._id",$entity['_id'])->get();

                    foreach ($results as $res) {
                        $this->updateParentData($entity, $res[$embedKey], $isDelete);
                        $parentId = ArrayUtil::shiftId($res);
                        $parentClass->getCollection()->where('_id', $parentId)->update($res);
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

    public function addRelations($type,$relations)
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

    public function getEmbeddedRelations($natural = true)
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
        array_map(function($k) use ($schema, &$validationInfo){
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

    public function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = App::make('resource-resolver');
        }
        return $this->resolver;
    }

    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

}

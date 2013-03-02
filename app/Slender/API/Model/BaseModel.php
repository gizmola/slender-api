<?php

namespace Slender\API\Model;

use Dws\Slender\Api\Resolver\ResourceResolver; // unused?
use Dws\Slender\Api\Support\Util\UUID;
use Dws\Slender\Api\Support\Query\FromArrayBuilder;

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
    protected $relations = [];

    public function findById($id, $no_cache=false)
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
            $this->embedChildData($data[$config['embedKey']],$childIntsance);
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

    public function getSchemaValidation()
    {
        return $this->schema;
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

            foreach ($notEmbeddedRelations as $resource => $config) {

                $childIntsance = $this->createRelatedClass($resource, $config);

                for ($i = 0; $i < count($entities); $i++) {
                    $this->embedChildData($entities[$i][$config['embedKey']], $childIntsance);
                }

            }

        }
    }

    private function createRelatedClass($resource, $config)
    {
        $resolver = \App::make('resource-resolver');
        $class = '\\' . $config['class'];
        $class = new $class($this->getConnection());
        $class->setRelations($resolver->buildModelRelations($resource, $this->site));
        return $class;
    }

    public function updateParents($entity)
    {

        try{

            $relations = $this->getRelations();
            if (!$relations || !is_array($relations) || !array_key_exists('parents', $relations)) {
                return true;
            }
            $parents = ['parents'];

            foreach ($parents as $resource => $config) {

                $parentClass = $this->createRelatedClass($resource, $config);
                $embeded = $parentClass->getEmbeddedRelations();

                if ($classConfig = $parentClass->getChildByClassName(get_class($this), $embeded)) {

                    $embedKey = $classConfig['embedKey'];

                    $results = $parentClass->getCollection()->where("{$embedKey}._id",$entity['_id'])->get();

                    foreach ($results as $res) {
                        $this->updateParentData($entity, $res[$embedKey]);
                        $parentId = $this->shiftId($res);
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

    public function shiftId(&$data)
    {
        $id = $data['_id'];
        unset($data['_id']);
        return $id;
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

}

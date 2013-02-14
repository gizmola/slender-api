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
        return \Cache::remember($this->collectionName . "_" . $id, \Config::get('cache.cache_time'), function() use($id){parent::find($id);});
    }

    /**
     * Get a collection of documents in this collection
     *
     * @param array $where
     * @param array $orders
     * @param type $limit
     * @param type $offset
     */
    public function findMany(array $where, array $fields, array $orders, &$meta, array $aggregate = null, $take = null, $skip = null, $count = null)
    {
        if (!\Config::get('cache.enabled') OR \Input::get('no_cache')) {
            return $this->findManyQuery($where, $fields,$orders, $meta, $aggregate, $take, $skip, $count);
        } else {
            /*
            * To distiunqish between ?foo=bar&a=b and ?a=b&foo=bar(same query)
            * we get the params as array, remove unused params, sort it and make it into a string again
            */
            $query = \Input::all();
            unset($query['purge_cache']);
            unset($query['no_cache']);
            asort($query);
            $query = http_build_query($query);
            
            if(\Input::get('purge_cache')) {
                \Cache::forget($query);
            }
            //@TODO: Below line is not pretty
            return \Cache::remember($query, \Config::get('cache.cache_time'), function() use ($where, $fields,$orders, $meta, $aggregate, $take, $skip, $count){ return $this->findManyQuery($where, $fields,$orders, $meta, $aggregate, $take, $skip, $count);});
        }
        
    }

    protected function findManyQuery($where, $fields, $orders, $meta, $aggregate, $take, $skip, $count) {

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

        if($count){
            $meta['count'] = $builder->count();
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

        $entities = [];

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
            $data['updated_at'] = new MongoDate();
        }

        $this->getCollection()->where('_id', $id)->update($data);
        $entity = $this->findById($id);

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
        $this->updateParents($id, true);
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
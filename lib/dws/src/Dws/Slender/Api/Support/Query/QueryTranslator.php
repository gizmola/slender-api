<?php namespace Dws\Slender\Api\Support\Query;

class QueryTranslator {
    
    protected $params;
    protected $builder;
    protected $meta = [];

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($name)
    {
        return (!empty($this->params[$name])) ? $this->params[$name] : null;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function setBuilder($builder)
    {
        $this->builder = $builder;
    }

    public function getBuilder()
    {
        return $this->builder;   
    }

    public function setMeta($meta, $name=null)
    {

        if (!$name) {
            $this->meta = $meta;  
        } else {
            $this->meta[$name] = $meta;    
        }

    }

    public function getMeta($name = null)
    {
        
        if ($name) {
            return (!empty($this->meta[$name])) ? $this->meta[$name] : null;    
        }
        
        return $this->meta;

    }

    public function translate()
    {
        $where = $this->getParam('where');
        $like = $this->getParam('like');
        $aggregate = $this->getParam('aggregate');
        $orders = $this->getParam('orders');
        $take = $this->getParam('take');
        $skip = $this->getParam('skip');
        $fields = $this->getParam('fields');
        $with = $this->getParam('with');
        $builder = $this->getBuilder();

        if ($where) {
            $builder = FromArrayBuilder::buildWhere($builder, $where);
        }

        if ($like) {
            $builder = FromArrayBuilder::buildLike($builder, $like);
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
                $this->meta['count'] = null;
            }


            $this->meta[$aggregate[0]] = $results;

            return [];

        }

        /*
        * the count() function calls get
        * internally which precludes setting
        * the "columns" when using get($fields)
        * 
        * additionally, the take and skip, will preclude
        * a count of the true number of documents matching
        * the criteria
        *
        * therefore, where clone the builder to count 
        */
        $builderClone = clone($builder);
        $this->meta['count'] = $builderClone->count();

        if ($orders) {
            $builder = FromArrayBuilder::buildOrders($builder,$orders);
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


        /*
        * the count() function calls get
        * internally which precludes setting
        * the "columns" when using get($fields)
        * so we must call count after
        * alternatively we could add a "setColumns"
        * function to our MongoModel
        */
        $this->meta['count'] = $builder->count();

        return $result;

    }

} 

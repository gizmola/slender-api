<?php namespace Dws\Slender\Api\Support\Query;

class QueryManager {
    
    protected $params;
    protected $builder;
    protected $meta = [];

    public function getParams()
    {
        return $this->params;
    }

    public function get($name)
    {
        return (!empty($this->params[$name])) ? $this->params[$name] : null;
    }

    public function setParams($params)
    {
        $this->params = $params;
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
        $where = $this->get('where');
        $aggregate = $this->get('aggregate');
        $orders = $this->get('orders');
        $take = $this->get('take');
        $skip = $this->get('skip');
        $fields = $this->get('fields');
        $with = $this->get('with');
        $builder = $this->getBuilder();

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
                $this->meta['count'] = null;
            }


            $this->meta[$aggregate[0]] = $results;

            return [];
            
        }

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
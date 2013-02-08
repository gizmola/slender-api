<?php

class Sites extends BaseModel{

    protected $collectionName = 'sites';

    protected $timestamp = true;

    protected $schema = [
        'url'       => ['required', 'url'],
        'title'     => ['required'],
        'slug'     => [],
    ];


    public function insert(array $data)
    {

        if(!isset($data['slug']) || $data['slug']){
            // slugify
            // $data['slug'] = $data['title'];
        }

        return parent::insert($data);
    }
}
<?php

namespace App\Model;

use Slug\Slugifier;

class Sites extends BaseModel
{

    protected $collectionName = 'sites';

    protected $timestamp = true;

    protected $schema = [
        'url'       => ['required', 'url'],
        'title'     => ['required'],
        'slug'     => [],
    ];


    public function insert(array $data)
    {

        if(!isset($data['slug']) || !$data['slug']){
            $data['slug'] = (new Slugifier)->slugify($data['title']);
        }

        return parent::insert($data);
    }
}
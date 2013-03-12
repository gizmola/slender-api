<?php

namespace Slender\API\Model;

use Slug\Slugifier;

class Sites extends \Slender\API\Model\BaseModel
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
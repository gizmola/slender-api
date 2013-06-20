<?php

namespace Slender\API\Model;

class Pages extends \Slender\API\Model\BaseModel
{
    
    protected $collectionName = 'pages';

    protected $schema = [
        'name'  => ['required'],
        'title'  => ['required'],
        'slug' => ['required'],
        'description' => [],
        'image' => [],
        'type' => [],
        'sections' => [],
        'created_at' => ['datetime'],
        'updated_at' => ['datetime'],
    ];

}
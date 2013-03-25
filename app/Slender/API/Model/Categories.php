<?php

namespace Slender\API\Model;

class Categories extends BaseModel
{

    protected $collectionName = 'categories';

    protected $timestamp = true;

    protected $schema = [
        'name'    => ['required'],
        'categories' => ['array']
    ];

}
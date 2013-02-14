<?php

namespace Slender\API\Model;

class Photos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'photos';
	
	protected $schema = [
    
        'title' => ['required'],
        'meta' => [
            'title' => [],
            'keywords' => [],
        ],
        'slug' => ['alpha_dash','required'],
        'photos' => [],
        'availability'  => [
            'sunrise' => ['date'],
            'sunset'  => ['date'],
        ],
        'created' => ['date'],
        'updated' => ['date'],

    ];

}
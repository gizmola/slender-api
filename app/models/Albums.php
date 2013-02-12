<?php

namespace App\Model;

class Albums extends BaseModel
{
	protected $collectionName = 'albums';
	
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
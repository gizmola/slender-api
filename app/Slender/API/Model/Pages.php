<?php

namespace Slender\API\Model;

class Pages extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'pages';
	
	protected $schema = [

        'id' => ['alpha_dash'],
        'title' => ['required'],
        'meta' => [
            'title' => [],
            'keywords' => [],
        ],
        'slug' => ['alpha_dash'],
        'body' => ['required'],
        'availability'  => [
            'sunrise' => ['date'],
            'sunset'  => ['date'],
        ],
        'created' => ['date'],
        'updated' => ['date'],

	];


}
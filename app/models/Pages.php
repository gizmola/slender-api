<?php

class Pages extends BaseModel
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
        'created' => [],
        'updated' => [],

	];


}
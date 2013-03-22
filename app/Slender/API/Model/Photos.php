<?php

namespace Slender\API\Model;

class Photos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'photos';

	protected $schema = [

        'title' => ['required'],
        'slug' => ['alpha_dash','required'],
        'meta' => [
            'keywords' => ['array'],
            'description' => [],
        ],
        'versions' => ['array'],

    ];

}
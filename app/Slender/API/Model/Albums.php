<?php

namespace Slender\API\Model;

class Albums extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'albums';
	
	protected $schema = [
    
        'title' => ['required'],
        'slug' => ['alpha_dash','required'],
        'photos' => ['array'],

    ];

}
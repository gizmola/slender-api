<?php

namespace App\Model;

class Episodes extends BaseModel
{
	protected $collectionName = 'episodes';

	protected $schema = array(
        'title' => array('type' => 'string'),
        'slug' => array('type' => 'string'),
        'description' => array('type' => 'string'),
        'season' 	=> array('type' => 'string'),
        'tags' => array('type' => 'array'),
        'created' 	=> array('type' => 'DateTime'),
        'updated' 	=> array('type' => 'DateTime'),
	);

}
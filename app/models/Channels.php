<?php

namespace App\Model;

class Channels extends BaseModel
{
	protected $collectionName = 'channels';

	protected $schema = array(
        'title' => array('type' => 'string'),
        'slug' => array('type' => 'string'),
        'description' => array('type' => 'string'),
        'tags' => array('type' => 'array'),
        'genre' 	=> array('type' => 'string'),
        'start_date' 	=> array('type' => 'DateTime'),
        'end_date' 	=> array('type' => 'DateTime'),
        'created' 	=> array('type' => 'DateTime'),
        'updated' 	=> array('type' => 'DateTime'),
	);

}
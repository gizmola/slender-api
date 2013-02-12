<?php

namespace Slender\API\Model;

class Videos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videos';

	protected $schema = array(
        'title' => array('type' => 'string'),
        'description' => array('type' => 'string'),
        'slug' => array('type' => 'string'),
        'premiere_date' 	=> array('type' => 'DateTime'),
        'rating' 	=> array('type' => 'Integer'),
        'genre' 	=> array('type' => 'string'),
        'episode_number' 	=> array('type' => 'Integer'),
        'season' 	=> array('type' => 'string'),
        'urls'	=> array (
            'source' 	=> array('type' => 'string'),
            'streaming' 	=> array('type' => 'string'),
            'thumbnail' 	=> array('type' => 'string'),
        ),
        'availability'	=> array (
    		'sunrise' 	=> array('type' => 'DateTime'),
    		'sunset'	=> array('type' => 'DateTime'),
        ),
        'created' 	=> array('type' => 'DateTime'),
        'updated' 	=> array('type' => 'DateTime'),
	);

}
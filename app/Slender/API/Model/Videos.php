<?php

namespace Slender\API\Model;

class Videos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videos';

	protected $schema = array(
        'title' => array('type' => 'Required'),
        'description' => array('type' => 'Required'),
        'slug' => array('type' => 'Required'),
        'premiere_date' 	=> array('type' => 'Date'),
        'rating' 	=> array('type' => 'Integer'),
        'genre' 	=> array('type' => 'Required'),
        'episode_number' 	=> array('type' => 'Integer'),
        'season' 	=> array('type' => 'Required'),
        'urls'	=> array (
            'source' 	=> array('type' => 'URL'),
            'streaming' 	=> array('type' => 'URL'),
            'thumbnail' 	=> array('type' => 'URL'),
        ),
        'availability'	=> array (
    		'sunrise' 	=> array('type' => 'Date'),
    		'sunset'	=> array('type' => 'Date'),
        ),
        'created' 	=> array('type' => 'Date'),
        'updated' 	=> array('type' => 'Date'),
	);

}
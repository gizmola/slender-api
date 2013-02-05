<?php

class Videos extends BaseModel
{
	protected $collectionName = 'videos';

	protected $schema = array(
        'title' => array('type' => 'string'),
        'description' => array('type' => 'string'),
        'slug' => array('type' => 'string'),
        'premiere_date' 	=> array('type' => 'DateTime'),
        'rating' 	=> array('type' => 'string'),
        'genre' 	=> array('type' => 'string'),
        'episode_number' 	=> array('type' => 'string'),
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
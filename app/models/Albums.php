<?php

class Albums extends BaseModel
{
	protected $collectionName = 'albums';
	
	protected $schema = array(

        'title' => array('type' => 'string'),
        'meta'	=> array (
    		'title' 	=> array('type' => 'string'),
    		'keywords'	=> array('type' => 'string'),
        ),
		'slug' => array('type' => 'string'),
		'photos' => array('type' => 'array'),
        'availability'	=> array (
    		'sunrise' 	=> array('type' => 'DateTime'),
    		'sunset'	=> array('type' => 'DateTime'),
        ),
		// created
		// updated
	);

}
<?php

class Pages extends BaseModel
{
	protected $collectionName = 'pages';
	
	protected $schema = array(

        'title' => array('type' => 'string'),
        'meta'	=> array (
    		'title' 	=> array('type' => 'string'),
    		'keywords'	=> array('type' => 'string'),
        ),
		'slug' => array('type' => 'string'),
		'body' => array('type' => 'string'),
        'availability'	=> array (
    		'sunrise' 	=> array('type' => 'DateTime'),
    		'sunset'	=> array('type' => 'DateTime'),
        ),
		// created
		// updated
	);


}
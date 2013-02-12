<?php

namespace Slender\API\Model;

class Photos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'photos';
	
	protected $schema = array(

        'title' => array('type' => 'string'),
        'meta'	=> array (
    		'title' 	=> array('type' => 'string'),
    		'keywords'	=> array('type' => 'string'),
        ),
		'slug' => array('type' => 'string'),
		'images' => array('type' => 'array'),
        'availability'	=> array (
    		'sunrise' 	=> array('type' => 'DateTime'),
    		'sunset'	=> array('type' => 'DateTime'),
        ),
		// created
		// updated
	);

}
<?php

namespace App\Models;

// use BaseModel;

class Sites extends BaseModel
{
	public static $collection = 'sites';
	// public $connection = 'default';

	public static $schema = array(

		// 'id'   => array(),
        'title' => array('type' => 'string'),
        'slug' => array('type' => 'string'),
	);
}
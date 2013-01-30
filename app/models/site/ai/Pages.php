<?php

namespace App\Model\Site\Ai;

use \Pages as BasePages;

class Pages extends BasePages{

	public static $schema = array(

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


	 // $news = array(
	 //        'title' => 'News II Title',
	 //        'meta'	=> array (
	 //    		'title' 	=> 'News Title',
	 //    		'keywords'	=> array('tag3','tag4'),
	 //        ),
	// 	'slug' => 'news-ii-title',
	// 	'body' => 'description',
	 //        'availability'	=> array (
	 //    		'sunrise' 	=> '',
	 //    		'sunset'	=> '',
	 //        ),
	// );
}
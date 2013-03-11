<?php

namespace Slender\API\Model;

class Videos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videos';

	protected $schema = array(
        'title'             => ['required'],
        'description'       => [],
        'slug'              => ['alpha_dash'],
        'premiere_date'     => ['date'],
        'rating'            => ['integer'],
        'genre'             => [],
        'episode_number' 	=> ['integer'],
        'season'            => ['integer'],
        'urls'	=> [
            'source' 	 => [],
            'streaming'  => [],
            'thumbnail'  => [],
        ],
        'cloud_filename' => [],
        'availability'	=> [
    		'sunrise' 	=> ['date'],
    		'sunset'	=> ['date'],
        ],
        'created' 	=> ['date'],
        'updated' 	=> ['date'],
	);

}
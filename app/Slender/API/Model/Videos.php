<?php

namespace Slender\API\Model;

class Videos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videos';

	protected $schema = array(
        'title'             => ['required', 'string'],
        'description'       => ['string'],
        'slug'              => ['alpha_dash'],
        'premiere_date'     => ['date'],
        'rating'            => ['int'],
        'genre'             => ['string'],
        'episode_number' 	=> ['int'],
        'season'            => ['int'],
        'urls'	=> [
            'source' 	 => ['string'],
            'streaming'  => ['string'],
            'thumbnail'  => ['string'],
        ],
        'cloud_filename' => ['string'],
        'availability'	=> [
    		'sunrise' 	=> ['date'],
    		'sunset'	=> ['date'],
        ],
        'created' 	=> ['date'],
        'updated' 	=> ['date'],
	);

}
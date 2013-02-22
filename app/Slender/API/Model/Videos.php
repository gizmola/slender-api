<?php

namespace Slender\API\Model;

class Videos extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videos';

	protected $schema = array(
        'title'             => ['required', 'string'],
        'description'       => ['string'],
        'slug'              => ['alpha_desh'],
        'premiere_date'     => ['datetime'],
        'rating'            => ['int'],
        'genre'             => ['string'],
        'episode_number' 	=> ['int'],
        'season'            => ['int'],
        'urls'	=> [
            'source' 	 => ['string'],
            'streaming'  => ['string'],
            'thumbnail'  => ['string'],
        ],
        'availability'	=> [
    		'sunrise' 	=> ['datetime'],
    		'sunset'	=> ['datetime'],
        ],
        'created' 	=> ['datetime'],
        'updated' 	=> ['datetime'],
	);

}
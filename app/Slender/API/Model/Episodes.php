<?php

namespace Slender\API\Model;

class Episodes extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'episodes';

	protected $schema = array(
        'title'         => ['required', 'string'],
        'slug'          => ['required', 'alpha_dash'],
        'description'   => ['string'],
        'season'        => ['string'],
        'tags'          => ['array'],
        'created'       => ['datetime'],
        'updated'       => ['datetime'],
	);
}
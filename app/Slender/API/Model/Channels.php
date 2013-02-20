<?php

namespace Slender\API\Model;

class Channels extends BaseModel
{
	protected $collectionName = 'channels';

	protected $schema = [
        'title'         => ['required', 'string'],
        'slug'          => ['required', 'string'],
        'description'   => ['string'],
        'tags'          => ['array'],
        'genre'         => ['string'],
        'start_date' 	=> ['date'],
        'end_date'      => ['date'],
        'created'       => ['datetime'],
        'updated'       => ['datetime'],
	];

}
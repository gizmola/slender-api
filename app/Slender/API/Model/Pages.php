<?php

namespace Slender\API\Model;

class Pages extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'pages';

	protected $schema = [

        'title'  => ['required'],
        'meta'   => [
            'title'    => ['string'],
            'keywords' => ['string'],
        ],
        'slug'  => ['alpha_dash'],
        'body'  => ['string'],
        'availability'  => [
            'sunrise' => ['datetime'],
            'sunset'  => ['datetime'],
        ],
        'created' => ['datetime'],
        'updated' => ['datetime'],
	];

}
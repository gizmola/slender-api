<?php

namespace Slender\API\Model;

class Videochannelss extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videochannels';

	protected $schema = array(
        'title'             => ['required', 'string'],
        'api_key'          => ['required', 'string'],
        'api_name'         => ['required', 'string'],
        'youtube_email'    => ['required', 'email'],
        'youtube_pass'     => ['required', 'string']
	);

}
<?php

namespace Slender\API\Model;

class Youtubechannels extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'youtubechannels';

	protected $schema = array(
        'title'             => ['required', 'string'],
        'apiKey'          => ['required', 'string'],
        'apiName'         => ['required', 'string'],
        'youtubeEmail'    => ['required', 'email'],
        'youtubePass'     => ['required', 'string']
	);

}
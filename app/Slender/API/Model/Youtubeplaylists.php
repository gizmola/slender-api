<?php

namespace Slender\API\Model;

class Youtubeplaylists extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'youtubeplaylists';

	protected $schema = array(
        'title'       => ['required', 'string'],
        'name'        => ['required', 'string'],
        'alias'       => ['required', 'string'],
        'channel'     => [
            'id' => ['required', 'string'],
            'title' => ['required', 'string']
        ]
	);

}
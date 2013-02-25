<?php

namespace Slender\API\Model;

class Videoqueues extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videoqueues';

	protected $schema = array(
        'video_id'             => ['required', 'string'],
        'distribution_id'          => ['required', 'string'],
        'status'         => ['required', 'string'],
        'created' => ['datetime'],
        'updated' => ['datetime']
	);

}
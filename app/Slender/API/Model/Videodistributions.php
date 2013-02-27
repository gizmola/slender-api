<?php

namespace Slender\API\Model;

class Videodistributions extends \Slender\API\Model\BaseModel
{
	protected $collectionName = 'videodistributions';

    const STATUS_PUBLISHED = 'published';
    const STATUS_NOT_PUBLISHED = 'not published';
    const STATUS_QUEUED = 'queued';
    const STATUS_IN_PROGRESS = 'in progress';

	protected $schema = array(
        'video_id'             => ['required', 'string'],
        'distribution_id'          => ['required', 'string'],
        'status'         => ['required', 'string'],
        'created' => ['datetime'],
        'updated' => ['datetime'],
        'attempts' => ['int']
	);

}
<?php

namespace Slender\API\Model\Site\Ai;

use Slender\API\Model\Photos as BasePhotos;

class Photos extends BasePhotos
{

	protected $relations = [
		'parents' => [
			'albums' => 'photos'
		],
		'children' => [],
	];

}
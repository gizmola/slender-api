<?php

namespace App\Model\Site\Ai;

use App\Model\Photos as BasePhotos;

class Photos extends BasePhotos
{

	protected $relations = [
		'parents' => [
			'albums' => 'photos'
		],
		'children' => [],
	];

}
<?php

namespace App\Model\Site\Ai;

use \Photos as BasePhotos;

class Photos extends BasePhotos
{

	protected $relations = [
		'parents' => ['albums'],
		'children' => [],
	];

}
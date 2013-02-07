<?php

namespace App\Model\Site\Ai;

use \Albums as BaseAlbums;

class Albums extends BaseAlbums
{

	protected $relations = [
		'parents' => [],
		'children' => [
			'photos' => true
		],
	];

}
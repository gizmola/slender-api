<?php

namespace App\Model\Site\Ai;

use App\Model\Albums as BaseAlbums;

class Albums extends BaseAlbums
{

	protected $relations = [
		'parents' => [],
		'children' => [
			'photos' => true
		],
	];

}
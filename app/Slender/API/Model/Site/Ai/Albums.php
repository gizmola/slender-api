<?php

namespace Slender\API\Model\Site\Ai;

use Slender\API\Model\Albums as BaseAlbums;

class Albums extends BaseAlbums
{

	protected $relations = [
		'parents' => [],
		'children' => [
			'photos' => true
		],
	];

}
<?php

namespace App\Controller\Site\Ai;

use \NewsController as BaseNewsController;
use \Response;

/**
 * NewsController for the AI site
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class NewsController extends BaseNewsController
{
	public function index()
	{
		// sample override just for demo purposes
		return Response::json(array(
			$this->getReturnKey() => array(
				array(
					'id' => 111,
					'headline' => 'My Headline 1 via overriden controller',
					'slug' => 'my-slug-1',
				),
				array(
					'id' => 222,
					'headline' => 'My Headline 2 via overriden controller',
					'slug' => 'my-slug-2',
				),
				array(
					'id' => 333,
					'headline' => 'My Headline 3 via overriden controller',
					'slug' => 'my-slug-3',
				),
			),
		));
	}
}

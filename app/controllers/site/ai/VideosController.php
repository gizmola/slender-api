<?php

namespace App\Controller\Site\Ai;

/**
 * VideosController for the AI site
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class VideosController extends \VideosController
{
	protected $site = 'ai';
	
	public function view($id)
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}
}

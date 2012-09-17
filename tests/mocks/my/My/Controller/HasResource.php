<?php

namespace My\Controller;

/**
 * A basic controller that has a resource
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class HasResource extends AbstractController
{
	public function httpGet($id = null)
	{
		$this->slender->response()->status(200);
		$this->slender->response()->body(json_encode(array(
			'resources' => array(
				$this->controllerResources['someResourceKey'],
			))));
	}
}

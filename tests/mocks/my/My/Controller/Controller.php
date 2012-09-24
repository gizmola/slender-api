<?php

namespace My\Controller;

use Dws\Slender\Controller\AbstractController;

/**
 * A concrete implementation of the AbstractController
 *
 * @author David Weinraub <david@papayasoft.com>
 */
class Controller extends AbstractController
{

	public function httpGetSingular($id)
	{
		$this->response->status(200);
		$this->response->body(json_encode(array(
			'k1' => 'v1',
		)));
	}
	
	public function httpGetPlural()
	{
		$this->response->status(200);
		$this->response->body(json_encode(array(
			'k1' => 'v1',
		)));
	}
}

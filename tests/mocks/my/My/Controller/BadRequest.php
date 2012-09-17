<?php

namespace My\Controller;

/**
 * A controller that returns badRequest
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class BadRequest extends AbstractController
{
	public function httpGet($id = null)
	{
		$this->slender->badRequest();
	}
	
	public function httpPost()
	{
		$this->slender->badRequest();
	}
	
	public function httpPut($id)
	{
		$this->slender->badRequest();
	}
	
	public function httpDelete($id)
	{
		$this->slender->badRequest();
	}
}

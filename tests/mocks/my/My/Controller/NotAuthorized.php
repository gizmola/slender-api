<?php

namespace My\Controller;

/**
 * A controller that return notAuthorized
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class NotAuthorized extends AbstractController
{
	public function httpGet($id = null)
	{
		$this->slender->notAuthorized();
	}
	
	public function httpPost()
	{
		$this->slender->notAuthorized();
	}
	
	public function httpPut($id)
	{
		$this->slender->notAuthorized();
	}
	
	public function httpDelete($id)
	{
		$this->slender->notAuthorized();
	}
}

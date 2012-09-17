<?php

namespace My\Controller;

/**
 * A controller that returns errors
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ReturnError extends AbstractController
{
	public function httpGet($id = null)
	{
		throw new \Exception('A GET exception');
	}
	
	public function httpPost()
	{
		throw new \Exception('A POST exception');
	}
	
	public function httpPut($id)
	{
		throw new \Exception('A PUT exception');
	}
	
	public function httpDelete($id)
	{
		throw new \Exception('A DELETE exception');
	}
}

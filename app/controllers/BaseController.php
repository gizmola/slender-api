<?php

/**
 * Base controller
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
abstract class BaseController extends Controller
{
	protected $site;

	public function getSite()
	{
		if (null == $this->site) {
			throw new \Exception('Site must be set in subclasses');
		}
		return $site;
	}
	
	public function setSite($site)
	{
		$this->site = (string) $site;
	}
}

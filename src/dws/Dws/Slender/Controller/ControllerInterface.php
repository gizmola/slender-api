<?php

namespace Dws\Slender\Controller;

/**
 * An interface for controllers called by the Slender framework
 * 
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
interface ControllerInterface
{
	/**
	 * Handle an HTTP GET request
	 * 
	 * @param mixed $id
	 */
	public function httpGet($id = null);

	/**
	 * Handle an HTTP POST request
	 */
	public function httpPost();
	
	/**
	 * Handle an HTTP PUT request
	 * 
	 * @param mixed $id
	 */
	public function httpPut($id);
	
	/**
	 * Handle an HTTP DELETE request
	 * 
	 * @param mixed $id
	 */
	public function httpDelete($id);
}

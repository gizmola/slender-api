<?php

namespace My\Controller;

use Dws\Slender\Slender;
use Dws\Slender\Controller\ControllerInterface;

/**
 * A base controller for tests
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class AbstractController implements ControllerInterface
{
	/**
	 * A Slender app instance
	 * 
	 * @var Slender;
	 */
	protected $slender;
	
	/**
	 * Resources for the controller
	 * 
	 * @var array
	 */
	protected $controllerResources = array();
	
	/**
	 * Constructor
	 * 
	 * @param \Dws\Slender\Slender $slender
	 * @param array $controllerResources
	 */
    public function __construct(Slender $slender, $controllerResources = array())
    {
		$this->slender = $slender;
		$this->controllerResources = $controllerResources;
    }

	public function httpDelete($id)
	{	
	}

	public function httpGet($id = null)
	{	
	}

	public function httpPost()
	{	
	}

	public function httpPut($id)
	{	
	}
	
}

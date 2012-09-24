<?php

namespace Dws\Slender\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

use Dws\Slender\Slender;

/**
 * A base controller implementation for use with Slender
 *
 * @author David Weinraub <david@papayasoft.com>
 */
class AbstractController
{

	/**
	 *
	 * @var Slender
	 */
	protected $slender;
	
	/**
	 * The HTTP request object
	 * 
	 * @var Request
	 */
	protected $request;
	
	/**
	 * The HTTP response object
	 * 
	 * @var Response
	 */
	protected $response;
	
	/**
	 * Construct
	 * 
	 * @param \Dws\Slender\Slender $slender
	 */
	public function __construct(Slender $slender)
	{
		$this->slender = $slender;
		$this->request = $slender->request();
		$thi->response = $slender->response();
	}
	
	
	/**
	 * Handler for unauthorized access
	 */
	public function notAuthorized($message = null)
	{
		if (null === $message){
			$message = 'Supplied credentials insufficient to access resource.';
		}
		$this->slender->haltMessages(401, $message);
	}

	/**
	 * A convenience wrapper for returning messages to the client
	 *
	 * @param int $code HTTP code
	 * @param string|array $messages
	 */
	public function haltMessages($code, $messages = array()){
		return $this->slender->haltMessages($code, $messages);
	}
	
	/**
	 * Handler for invalid API requests
	 *
	 * @param mixed $errors Errors to display, tpyically as an array
	 *	   However, to accommodate flexible usage, we will attempt to
	 *     wrap the munge the given datatype into an single-dimensional
	 *     array of messages.
	 */
	public function badRequest($errors = null)
	{
		if (null === $errors){
			$errors = array('Generic error');
		}
		if (is_scalar($errors)){
			$errors = array($errors);
		}

		$errors = self::flattenArray($errors);

		$isStackTrace = (bool) $this->request->params('st');
		if ($isStackTrace) {
			ini_set('xdebug.collect_vars', 'on');
			ini_set('xdebug.collect_params', '4');
            if (function_exists('xdebug_get_function_stack')){
    			$errors[] = xdebug_get_function_stack();
            }
		}

		$this->haltMessages(400, $errors);
	}
	
	/**
	 * Flatten an array to only the leaves
	 * 
	 * @param array $array
	 * @return array
	 */
	public static function flattenArray($array)
	{
	    $return = array();
	    array_walk_recursive($array, function($a) use (&$return) { 
			$return[] = $a;
		});
	    return $return;
	}
}

<?php

namespace Dws\Slender;

use Slim\Slim;

/**
 * Application object
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class Slender extends Slim
{

	/**
	 * Constructor
	 * 
	 * @param array|null $options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);		
		$this->init();
	}
	
	/**
	 * Init
	 */
	protected function init()
	{
		// For use in callbacks
		$app = $this;

		// Configure response
		$app->response()->header('Content-type', 'application/json');

		// Configure not-found handler
		$app->notFound(function () use ($app) {
			$app->haltMessages(404, 'Resource not found');
		});

		// Configure error handler
		$app->error(function(\Exception $e) use ($app) {
			$messages = array($e->getMessage());
			$app->haltMessages(500, $messages);
		});

	}
	
	/**
	 * A convenience wrapper for returning messages to the client
	 *
	 * @param int $code HTTP code
	 * @param string|array $messages
	 */
	public function haltMessages($code, $messages = array())
	{
		if (is_string($messages)){
			$messages = array($messages);
		}
		if (!is_array($messages)){
			throw new \RuntimeException('An array of messages is required');
		}		
		$response = json_encode(array(
			'messages' => $messages,
		));
		$this->cleanBuffer();
		$this->response->status($code);
		$this->response->body($response);		
	}
	
	/**
	 * Instantiate and call controller
	 * 
	 * @param string $controllerName
	 * @param mixed $id
	 */
//	public function callAction($controllerName, $id = null)
//	{
//		$app = $this;
//		$controllerClass = $this->controllerNamespace . '\\' . ucfirst($controllerName);
//		$method = 'http' . ucfirst(strtolower($app->request()->getMethod()));		
//		if (class_exists($controllerClass, true)) {
//			$controller = new $controllerClass($app, $this->controllerResources);
//			if (is_callable(array($controller, $method))) {
//				try {
//					call_user_func(array($controller, $method), $id);
//				} catch (\Exception $e){
//					$this->error($e);
//				}
//			} else {
//				$app->notFound();
//			}
//		} else {
//			$app->notFound();
//		}
//	}
	
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

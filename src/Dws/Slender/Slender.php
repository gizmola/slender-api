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
	 * The namespace for controllers
	 * 
	 * @var string
	 */
	protected $controllerNamespace = '';
	
	/**
	 * An array of resources to pass to the controller on instantiation
	 * 
	 * @var array
	 */
	protected $controllerResources = array();
	
	/**
	 * Constructor
	 * 
	 * @param array|null $options
	 */
	public function __construct($options = null)
	{
		if ($options && is_array($options)){
			if (array_key_exists('controllerNamespace', $options)){
				$this->controllerNamespace = $options['controllerNamespace'];
				unset($options['controllerNamespace']);
			}
			
			if (array_key_exists('controllerResources', $options)){
				$this->controllerResources = $options['controllerResources'];
				unset($options['controllerResources']);
			}
		}
		
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
		// $app->response()->header('Access-Control-Allow-Origin','*');
		// $app->response()->header('Access-Control-Allow-Headers','X-Requested-With');

		// Configure not-found handler
		$app->notFound(function () use ($app) {
			$app->haltMessages(404, 'Resource not found');
		});

		// Configure error handler
		$app->error(function(\Exception $e) use ($app) {
			$messages = array($e->getMessage());
			$app->haltMessages(500, $messages);
		});

		// Add routes
		$app->map('/:controllerName(/(:id(/)))', function($controllerName, $id = null) use ($app){
			$app->callAction($controllerName, $id);
		})->via('GET');
		
		$app->map('/:controllerName/:id(/)', function($controllerName, $id) use ($app){
			$app->callAction($controllerName, $id);
		})->via('PUT', 'DELETE');
		
		$app->map('/:controllerName(/)', function($controllerName) use ($app){
			$app->callAction($controllerName);			
		})->via('POST');
	}
	
	/**
	 * Instantiate and call controller
	 * 
	 * @param string $controllerName
	 * @param mixed $id
	 */
	public function callAction($controllerName, $id = null)
	{
		$app = $this;
		$controllerClass = $this->controllerNamespace . '\\' . ucfirst($controllerName);
		$method = 'http' . ucfirst(strtolower($app->request()->getMethod()));		
		if (class_exists($controllerClass, true)) {
			$controller = new $controllerClass($app, $this->controllerResources);
			if (is_callable(array($controller, $method))) {
				try {
					call_user_func(array($controller, $method), $id);
				} catch (\Exception $e){
					$this->error($e);
				}
			} else {
				$app->notFound();
			}
		} else {
			$app->notFound();
		}
	}

	/**
	 * Handler for unauthorized access
	 */
	public function notAuthorized($message = null)
	{
		if (null === $message){
			$message = 'Supplied credentials insufficient to access resource.';
		}
		$this->haltMessages(401, $message);
	}

//	/**
//	 * Handler for not allowed method
//	 */
//	public function notAllowed($message = null)
//	{
//		if (null === $message){
//			$message = 'Method not allowed';
//		}
//		$this->haltMessages(405, $message);
//	}

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
		$this->response()->status($code);
		$this->response()->body($response);
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

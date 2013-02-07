<?php

namespace Dws\Slender\Api\Controller\Helper;

use Illuminate\Support\Facades\Input;

/**
 * A class to convert query parameters into an array or string
 *
 * @author Juni Samos <juni.samos@diamondwebservices.com>
 */
class Params{

	/**
	 * Parse the named query param into an array.
	 * @param  string | array $input
	 * @param  string  $delim
	 * @return array
	 */
	public static function parse($input, $delim=false)
	{


		if (!$input) {

			return $input;
		
		} elseif (!$delim) {
			
			if (is_array($input)) {
				array_walk_recursive($input, array(new Params,'floatize'));
				return $input;
			} else {
				return (is_numeric($input)) ? (float)$input : $input;
			}
	
		} elseif (!is_array($input)) {

			$array = explode($delim, $input);
			array_walk_recursive($array, array(new Params,'floatize'));

			return $array; 
	
		} else {

			$array = array();

			foreach ($input as $v) {
				$array[] = explode($delim,$v);
			}

			array_walk_recursive($array, array(new Params,'floatize'));

			return $array;
		}

	}
	/**
	 * Convert all numeric strings to float.
	 * @param  string  $item
	 * @param  string  $key
	 * @return void
	 */
	public static function floatize(&$item,$key){
	   	
	   	if (is_numeric($item)) {
	   		$item = (float)$item;
	   	}

	}
	/**
	 * Parse the filter query param to an array 
	 * arrays containing 2 or 3 elements
	 * @return array
	 */
	public static function getWhere(Array $filters = null)
	{
		$input = ($filters) ? $filters : Input::get('where');
		return self::parse($input, ":");
	}
	/**
	 * Parse the orders query param 
	 * to an array of arrays containg 2 elements
	 * @param string $order
	 * @return array
	 */
	public static function getOrders($order = null)
	{
		
		$input = ($order) ? $order : Input::get('order');
		$orders = self::parse($input, ",");

		if (!$orders) {
			return;
		}

		$array = array();
	
		foreach ($orders as $o) {
			$array[] = explode(':', $o);	
		}

		return $array;

	}
	/**
	 * Parse the fields query param 
	 * to an array of strings
	 * @return array
	 */
	public static function getFields($fields = null)
	{
		$input = ($fields) ? $fields : Input::get('fields');
		return self::parse($input, ",");
	}
	/**
	 * Parse the take query param 
	 * an integer
	 * @return int
	 */
	public static function getTake()
	{
		return (is_numeric(Input::get('take'))) ? (int)Input::get('take') : false;
	}
	/**
	 * Parse the take query param 
	 * an integer
	 * @return int
	 */
	public static function getSkip()
	{
		return (is_numeric(Input::get('skip'))) ? (int)Input::get('skip') : false;
	}

	public static function getCount($count = 0)
	{
		$input = ($count) ? $count : Input::get('count');
		return (is_numeric($input)) ? (int)$input : 0;
	}

	public static function getAggregate($aggregate = null)
	{
		$input = ($aggregate) ? $aggregate : Input::get('aggregate');
		return self::parse($input, ":");
	}


}
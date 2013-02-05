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
	 * @param  string  $name
	 * @param  string  $delim
	 * @return array
	 */
	public static function parse($name, $delim=false)
	{

		$input = Input::get($name);

		if (!$delim || !$input) {

			return $input;
	
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
	public static function getFilters()
	{
		return self::parse('filter', ":");
	}
	/**
	 * Parse the orders query param 
	 * to an array of arrays containg 2 elements
	 * @return array
	 */
	public static function getOrders()
	{
		
		$orders = self::parse('order', ",");

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
	public static function getFields()
	{
		return self::parse('fields', ",");
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


}
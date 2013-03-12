<?php

namespace Dws\Slender\Api\Controller\Helper;

use Illuminate\Support\Facades\Input;

/**
 * A class to convert query parameters into an array or string
 *
 * @author Juni Samos <juni.samos@diamondwebservices.com>
 */
class Params{

	protected static $dontCast = [];

	/**
	 * Parse the named query param into an array.
	 * @param  string | array $input
	 * @param  string  $delim
	 * @return array
	 */
	public static function parse($input, $delim=false)
	{

		if (!$input || !$delim) {

			return $input;
		
		} elseif (!is_array($input)) {

			$array = explode($delim, $input);
			return $array; 
	
		} else {

			$array = array();

			foreach ($input as $v) {
				$array[] = explode($delim,$v);
			}

			return $array;
		}

	}
	/**
	 * Convert all strings to datatype that can match mongo.
	 * @param  string  $item
	 * @return void
	 */
	public static function castWhereValues(&$where)
	{

		for ($i = 0; $i < count($where); $i++) {

			$valueStore = count($where[$i]) -1;
			$key = $where[$i][0];

			if (!in_array($key, self::getDontCast())) {

				//double and int types
				if (is_numeric($where[$i][$valueStore])) {
					$where[$i][$valueStore] = (float)$where[$i][$valueStore];	
				}

				/**
				* @todo date types
				*/

			}

		}

	}


	/**
	 * Parse the filter query param to an array 
	 * arrays containing 2 or 3 elements
	 * @return array
	 */
	public static function getWhere(Array $where = null)
	{
		$input = ($where) ? $where : Input::get('where');
		$input =  self::parse($input, ":");
		//where parameters need to be casted correctly
		self::castWhereValues($input);
		//array_walk_recursive($input, array(new Params,'cast'));
		return $input;
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

	public static function getAggregate($aggregate = null)
	{
		$input = ($aggregate) ? $aggregate : Input::get('aggregate');
		return self::parse($input, ":");
	}

	public static function getWith($embed =null)
	{
		$input = ($embed) ? $embed : Input::get('with');
		return self::parse($input, ":");	
	}

	public static function setDontCast(Array $array)
	{
		self::$dontCast = $array;
	}

	public static function getDontCast()
	{
		return self::$dontCast;
	}


}
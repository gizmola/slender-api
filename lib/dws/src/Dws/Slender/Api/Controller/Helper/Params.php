<?php

namespace Dws\Slender\Api\Controller\Helper;

use Illuminate\Support\Facades\Input;


class Params{

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

	public static function floatize(&$item,$key){
	   	
	   	if (is_numeric($item)) {
	   		$item = (float)$item;
	   	}

	}

	public static function getFilters()
	{
		return self::parse('filter', ":");
	}

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

	public static function getFields()
	{
		return self::parse('fields', ",");
	}

	public static function getTake()
	{
		return (is_numeric(Input::get('take'))) ? (int)Input::get('take') : false;
	}

	public static function getSkip()
	{
		return (is_numeric(Input::get('skip'))) ? (int)Input::get('skip') : false;
	}


}
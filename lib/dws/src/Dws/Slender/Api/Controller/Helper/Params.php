<?php

namespace Dws\Slender\Api\Controller\Helper;

use Illuminate\Support\Facades\Input;


class Params{

	public static function parse($name, $delim=false)
	{

		$input = Input::get($name);

		if (is_array($input)) {
			
			if (!$delim) {
				throw new \InvalidArgumentException ('No delimiter provided for array input');
			}

			$array = array();

			foreach ($input as $v) {
				$arr = explode($delim,$v);
				$array[$arr[0]] = $arr[1];
			}

			return $array;

		} elseif($delim) {

			return explode($delim,$input);
		
		} else {
		
			return $input;
		
		}

	}

	public static function getFilters()
	{
		return self::parse('filter', ":");
	}

	public static function getOrders()
	{
		return self::parse('order', ",");
	}

	public static function getFields()
	{
		return self::parse('fields');
	}

}
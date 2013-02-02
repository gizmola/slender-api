<?php

namespace Dws\Slender\Api\Support\Query;

class FromArrayBuilder{

	public static function buildWhere($queryBuilder, $array)
	{


		$equals = array_filter($array, 
			function($x)
			{
				return count($x) == 2;
			}
		);

		$ranges = array_filter($array, 
			function($x)
			{
				return count($x) != 2;
			}
		);

		foreach ($equals as $data) {
			$queryBuilder = self::appendWhere($queryBuilder, $data);	
		}



		foreach ($ranges as $data) {
			$queryBuilder = self::appendWhere($queryBuilder, $data);	
		}

		return $queryBuilder;	

	}

	public static function buildOrders($queryBuilder, $array)
	{
		
		foreach ($array as $order) {
			$queryBuilder = $queryBuilder->orderBy($order[0],$order[1]);	
		}

		return $queryBuilder;

	}


	public static function appendWhere($builder, $data)
	{
		if (count($data) == 3) {
			$function = 'where' . ucfirst($data[1]);
			return $builder->$function($data[0],$data[2]);
		} elseif (count($data) == 2) {
			return $builder->where($data[0],$data[1]);	
		}
	}

}
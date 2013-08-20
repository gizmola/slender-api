<?php

namespace Dws\Slender\Api\Support\Query;

/**
 * A class to query build from an array
 *
 * @author Juni Samos <juni.samos@diamondwebservices.com>
 */
class FromArrayBuilder{
	/**
	 * Append all where conditions to the query builder.
	 * @param  queryBuilder LMongo\Query\Builder
	 * @param  array  $array
	 * @return LMongo\Query\Builder
	 */
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

	/**
	 * Append all Like conditions to the query builder.
	 * @param  queryBuilder LMongo\Query\Builder
	 * @param  array  $array
	 * @return LMongo\Query\Builder
	 */
	public static function buildLike($queryBuilder, $array)
	{


		foreach ($array as $data) {
			$fields = explode(",", $data[0]);
			foreach ($fields as $field) {
				$queryBuilder->orWhereRegex($field, "/.*{$data[1]}.*/");	
			}
		}

		return $queryBuilder;	

	}

	/**
	 * Append a single where to the query builder.
	 * @param  queryBuilder LMongo\Query\Builder
	 * @param  array  $data
	 * @return LMongo\Query\Builder
	 */
	public static function appendWhere($builder, $data)
	{
		//data of 3 items are range conditions
		//data of 2 items are equals conditions
		if (count($data) == 3) {

			$function = (in_array($data[1], ['or', 'nor'])) ?
				$data[1] . 'Where'
				: 'where' . ucfirst($data[1]);

			return $builder->$function($data[0],$data[2]);
		} elseif (count($data) == 2) {
			return $builder->where($data[0],$data[1]);	
		}
	}

	/**
	 * Append where order statments to the query builder.
	 * @param  queryBuilder LMongo\Query\Builder
	 * @param  array  $array
	 * @return LMongo\Query\Builder
	 */
	public static function buildOrders($queryBuilder, $array)
	{
		
		foreach ($array as $order) {
			$queryBuilder = $queryBuilder->orderBy($order[0],$order[1]);	
		}

		return $queryBuilder;

	}
}

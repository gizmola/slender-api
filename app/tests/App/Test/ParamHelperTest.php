<?php

namespace App\Test;

use Dws\Slender\Api\Controller\Helper\Params as ParamsHelper;

class ParamHelperTest extends TestCase 
{

	public function testParseReturnsStringFromString()
	{
		$input = 'some-string';
		$params = ParamsHelper::parse($input);
		$this->assertEquals($input,$params);
	}

	public function testParseReturnsNullFromNull()
	{
		$input = null;
		$params = null;
		$params = ParamsHelper::parse($input);
		$this->assertEquals($input,$params);
	}

	public function testParseReturnsSameArray()
	{
		$input = array('1','2');
		$output = array((float)1, (float)2);
		$params = ParamsHelper::parse($input);
		$this->assertEquals($output,$params);
	}	

	public function testParseReturnsArrayFromDelimString()
	{
		$input = 'some,string';
		$output = array('some','string');
		$params = ParamsHelper::parse($input, ",");
		$this->assertEquals($output,$params);
	}

	public function testParseReturnsAssociativeArrayFromArray()
	{
		$input = array('season:gte:10', 'lastname:doe');
		$output = array(array('season', 'gte', (float)10), array('lastname','doe'));
		$params = ParamsHelper::parse($input, ":");
		$this->assertEquals($output,$params);	
	}

	public function testCanParseWhereParamIntoArray()
	{
		$input = array('season:gte:10', 'lastname:doe');
		$output = array(array('season', 'gte', (float)10), array('lastname','doe'));
		$params = ParamsHelper::getWhere($input);
		$this->assertEquals($output,$params);
	}

	public function testCanParseFieldsParamIntoArray()
	{
		$input = 'field1,field2';
		$output = array('field1', 'field2');
		$params = ParamsHelper::getFields($input);
		$this->assertEquals($output,$params);
	}

	public function testCanParseOrdersIntoArray()
	{
		$input = 'season:desc,lastname:asc';
		$output = array(array('season','desc'),array('lastname','asc'));
		$params = ParamsHelper::getOrders($input);
		$this->assertEquals($output,$params);
	}

	public function testCanParseCount()
	{
		$input = 1;
		$params = ParamsHelper::getCount($input);
		$this->assertEquals(1,$params);
	}

	public function testCanParseAggregateToArray()
	{

		$input = 'sum:orders';
		$output = ['sum','orders'];
		$params = ParamsHelper::getAggregate($input);
		$this->assertEquals($output,$params);

		$input = 'count';
		$output = ['count'];
		$params = ParamsHelper::getAggregate($input);
		$this->assertEquals($output,$params);

	}

	public function testCanParseEmbedParamIntoArray()
	{
		$input = ['parents:1', 'children:0'];
		$output = [['parents',(float)1],['children',(float)0]];
		$params = ParamsHelper::getWith($input);
		$this->assertEquals($output,$params);
	}

}
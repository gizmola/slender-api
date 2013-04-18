<?php

namespace App\Test;

use Dws\Slender\Api\Controller\Helper\Params as ParamsHelper;

class ParamHelperTest extends TestCase 
{

	public function testCanSetStaticDontCast()
	{
		$dontCast = ['zipcode','phone'];
		ParamsHelper::setDontCast($dontCast);
		$output = ParamsHelper::getDontCast();
		$this->assertEquals($dontCast,$output);
	}

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
		$params = ParamsHelper::parse($input);
		$this->assertSame($input,$params);
	}	

	public function testParseReturnsArrayFromDelimString()
	{
		$input = 'some,string';
		$output = array('some','string');
		$params = ParamsHelper::parse($input, ",");
		$this->assertSame($output,$params);
	}

	public function testParseReturnsAssociativeArrayFromArray()
	{
		$input = array('season:gte:10', 'lastname:doe');
		$output = array(array('season', 'gte', '10'), array('lastname','doe'));
		$params = ParamsHelper::parse($input, ":");
		$this->assertSame($output,$params);	
	}

	public function testCanCastWhereParams()
	{
		$input = array('season:gte:10', 'lastname:doe');
		$output = array(array('season', 'gte', (float)10), array('lastname','doe'));
		$params = ParamsHelper::getWhere($input);
		$this->assertSame($output,$params);

		$input = array('birthday:gte:Date(123456)');
		$params = ParamsHelper::getWhere($input);
		$this->assertInstanceOf('MongoDate', $params[0][2]);

	}

	public function testDoesNotCastExcluded()
	{
		
		$dontCast = ['zipcode','phone'];
		ParamsHelper::setDontCast($dontCast);

		$input = array('season:gte:10', 'phone:3105551212');
		$output = array(array('season', 'gte', (float)10), array('phone','3105551212'));
		$params = ParamsHelper::getWhere($input);
		$this->assertSame($output,$params);	
	}

	public function testCanParseFieldsParamIntoArray()
	{
		$input = 'field1,field2';
		$output = array('field1', 'field2');
		$params = ParamsHelper::getFields($input);
		$this->assertSame($output,$params);
	}

	public function testCanParseOrdersIntoArray()
	{
		$input = 'season:desc,lastname:asc';
		$output = array(array('season','desc'),array('lastname','asc'));
		$params = ParamsHelper::getOrders($input);
		$this->assertSame($output,$params);
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
		$this->assertSame($output,$params);

	}

	public function testCanParseEmbedParamIntoArray()
	{
		$input = ['parents:1', 'children:0'];
		$output = [['parents','1'],['children','0']];
		$params = ParamsHelper::getWith($input);
		$this->assertSame($output,$params);
	}

}
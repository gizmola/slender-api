<?php

use Dws\Slender\Api\Support\Query\FromArrayBuilder;
use LMongo\Query\Builder;
use LMongo\Database;

class FromArrayToBuilderTest extends TestCase 
{

	public function testCanCreateQueryBuilderObject()
	{
		$builder = new Builder(new Database(0, 0, 0));
		$this->assertInstanceOf('LMongo\Query\Builder', $builder);
	}

	public function testCanAddWheresToBuilder()
	{
		$builder = new Builder(new Database(0, 0, 0));
		$conditions = array(array('age', 'gte', 40), array('lastname','doe'));
		$builder = FromArrayBuilder::buildWhere($builder, $conditions);

		$wheres = $builder->wheres;

		$this->assertEquals(2, count($wheres));	
		$this->assertArrayHasKey('column', $wheres[0]);
		$this->assertEquals('lastname', $wheres[0]['column']);
		$this->assertArrayHasKey('value', $wheres[0]);
		$this->assertEquals('doe', $wheres[0]['value']);

		$this->assertArrayHasKey('column', $wheres[1]);
		$this->assertEquals('age', $wheres[1]['column']);
		$this->assertArrayHasKey('value', $wheres[1]);
		$this->assertInternalType('array', $wheres[1]['value']);
		$this->assertArrayHasKey('$gte', $wheres[1]['value']);
		$this->assertEquals(40, $wheres[1]['value']['$gte']);
	}

	public function testCanAppendOrdersToBuilder()
	{

		$builder = new Builder(new Database(0, 0, 0));
		$orders = array(array('season','desc'),array('lastname','asc'));
		$builder = FromArrayBuilder::buildOrders($builder, $orders);

		$orders = $builder->orders;
		$this->assertInternalType('array', $orders);
		$this->assertArrayHasKey('season',$orders);
		$this->assertArrayHasKey('lastname',$orders);
		$this->assertEquals(-1, $orders['season']);
		$this->assertEquals(1, $orders['lastname']);
		
	}

}
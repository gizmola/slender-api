<?php

use \BaseModel;

class BaseModelTest extends TestCase 
{

	public function testCanAddChildRelation()
	{
		$model = new BaseModel;	
		$child = ['child'=> true];
		$model->addChildRelations($child);
		$children = $model->getRelations()['children'];
		$this->assertArrayHasKey('child', $children);	
	}

	public function testCanGetEmbeddedChildRelations()
	{
		$model = new BaseModel;	
		$child = ['embeddedChild'=> true, 'otherchild' => false];
		$model->addChildRelations($child);

		$embeddedChildren = $model->getEmbeddedRelations();
		$this->assertEquals(1,count($embeddedChildren));
		$this->assertEquals(true, in_array('embeddedChild', $embeddedChildren));
	}

	public function testEmbedChildArray()
	{

		$model = new BaseModel;

		$parentData = [
			'name' => 'album1',
			'photos' => [1],
		];

		$modelSpy = $this->getMock('BaseModel');
		$modelSpy->expects($this->any())
			->method('findById')
			->with(1)
			->will($this->returnValue(['name'=>'photo1', 'description' => 'a pretty pic']));
		$model->embedChildData($parentData['photos'], $modelSpy);
		$this->assertInternalType('array', $parentData['photos'][0]);
		$this->assertArrayHasKey('name', $parentData['photos'][0]);
	}

} 
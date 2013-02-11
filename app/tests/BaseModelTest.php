<?php

use App\Model\BaseModel;

class BaseModelTest extends TestCase 
{
 	public function testCanAddChildRelation()
	{

		$model = new BaseModel;	
		$relations = ['child'=> true];
		$model->addRelations('children',$relations);
		$children = $model->getRelations()['children'];
		$this->assertArrayHasKey('child', $children);	
	}

	public function testCanGetEmbeddedChildRelations()
	{
		$model = new BaseModel;	
		$relations = ['embeddedChild'=> true, 'otherchild' => false];
		$model->addRelations('children',$relations);

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

		$modelSpy = $this->getMock('App\Model\BaseModel');
		$modelSpy->expects($this->exactly(1))
			->method('findById')
			->with(1)
			->will($this->returnValue(['name'=>'photo1', 'description' => 'a pretty pic']));
		$model->embedChildData($parentData['photos'], $modelSpy);
		$this->assertInternalType('array', $parentData['photos'][0]);
		$this->assertArrayHasKey('name', $parentData['photos'][0]);
	}

	public function testCanAddParentRelations()
	{

		$model = new BaseModel;	
		$relations = ['parent' => 'child'];
		$model->addRelations('parents',$relations);
		$parents = $model->getRelations()['parents'];
		$this->assertEquals(true,in_array('parent', array_keys($parents)));

	}

	public function testCanReportIfChildIsEmbedded()
	{

		$model = new BaseModel;	
		$relations = ['embeddedChild'=> true, 'otherchild' => false];
		$model->addRelations('children',$relations);

		$embedded = $model->isEmbedded('embeddedChild');
		$otherchild = $model->isEmbedded('otherchild');

		$this->assertEquals(true,$embedded);
		$this->assertEquals(false,$otherchild);

	}

	public function testCanRetrieveParentKeyName()
	{

		$model = new BaseModel;	
		$relations = ['parent' => 'child'];
		$model->addRelations('parents',$relations);

	}

	public function testUpdateParentDataWithNewChildData()
	{
		$parentData = [
			['_id' => '123', 'location' => 'path/to/file_123'],
			['_id' => '123456', 'location' => 'path/to/file_123456'],
		];

		$childData = ['_id' => '123', 'location' => 'new/path/to/file_123'];

		$model = new BaseModel;	
			
		$index = $model->updateParentData($childData, $parentData);
		$this->assertNotSame(null, $index);

		$this->assertNotSame($parentData[1], $childData);
		$this->assertSame($parentData[0], $childData);

		$index = $model->updateParentData($childData, $parentData, true);

		$this->assertEquals(1,count($parentData));
		$this->assertEquals('123456',array_shift($parentData)['_id']);

	}
	
} 
<?php

namespace App\Test\Model;

use Slender\API\Model\BaseModel;
use App\Test\TestCase;
use App\Test\Mock\Model\PartialUpdateWithValidation as PartialUpdateModel;
use Dws\Utils;

class BaseModelTest extends TestCase
{
 	public function testCanAddChildRelation()
	{

		$relations = [
			'parents' => [
		    	'my-parent-1' => [
		        	'class' => 'My\Parent\Class\Name',
		        ],
		    ],
		    'children' => [
		    	'my-child-1' => [
		        	'class' => 'My\Child\Class\Name',
		            'embed' => true, // or false
		            'embedKey' => 'sweet-child-of-mine',
		        ],
		    ],
		];

		$model = new BaseModel;
		$model->setRelations($relations);
		$children = $model->getRelations()['children'];
		$this->assertArrayHasKey('my-child-1', $children);
	}

	public function testCanAddRelation()
	{

		$relations = [
			'my-child-1' => [
				'class' => 'My\Child\Class\Name',
				'embed' => true, // or false
				'embedKey' => 'sweet-child-of-mine',
			]
		];

		$model = new BaseModel;
		$model->addRelations('children',$relations);
		$children = $model->getRelations()['children'];
		$this->assertArrayHasKey('my-child-1', $children);

	}

	public function testCanGetEmbeddedChildRelations()
	{
		$model = new BaseModel;

		$relations = [
			'embedded-child' => [
				'class' => 'My\Child\Class\Name',
				'embed' => true, // or false
				'embedKey' => 'sweet-child-of-mine',
			],
			'not-embedded-child' => [
				'class' => 'My\Child\Class\Name',
				'embed' => false, // or false
				'embedKey' => 'sweet-child-of-mine',
			]
		];

		$model->addRelations('children',$relations);

		$embeddedChildren = $model->getEmbeddedRelations();
		$this->assertEquals(1,count($embeddedChildren));
		$this->assertArrayHasKey('embedded-child', $embeddedChildren);

		$notEmbeddedChildren = $model->getEmbeddedRelations(false);
		$this->assertEquals(1,count($notEmbeddedChildren));
		$this->assertArrayHasKey('not-embedded-child', $notEmbeddedChildren);

	}

	public function testUpdateParentDataWithNewChildData()
	{
		$parentData = [
			['_id' => '123', 'location' => 'path/to/file_123'],
			['_id' => '123456', 'location' => 'path/to/file_123456'],
		];

		$childData = ['_id' => '123', 'location' => 'new/path/to/file_123'];

		$model = new BaseModel;
		$index = $model->updateParentData($childData, $parentData, 'has-many');
		$this->assertNotSame($parentData[1], $childData);
		$this->assertSame($parentData[0], $childData);

		$index = $model->updateParentData($childData, $parentData, 'has-many', true);
		$this->assertEquals(1,count($parentData));
		$this->assertEquals('123456',array_shift($parentData)['_id']);

	}

	public function testCanGetEmbededChildParent()
	{

		$model = new BaseModel;

		$relations = [
			'embedded-child' => [
				'class' => 'My\Child\Class\EmbeddedClass',
				'embed' => true, // or false
				'embedKey' => 'sweet-child-of-mine',
			],
			'not-embedded-child' => [
				'class' => 'My\Child\Class\NotEmbeddedClass',
				'embed' => false, // or false
				'embedKey' => 'sweet-child-of-mine',
			]
		];

		$model->addRelations('children',$relations);
		$embedded = $model->getEmbeddedRelations();
		$embededChild = $model->getChildByClassName('My\Child\Class\EmbeddedClass',$relations);
		$this->assertSame($relations['embedded-child'],$embededChild);

	}

    public function testPartialUpdate()
    {
        $data = [
            // missing required field
            'my-optional-field' => 'xxx',
        ];

        $model = new PartialUpdateModel();
        $this->assertTrue($model->isValid($data, true));  // isPartial = true

        $model = new PartialUpdateModel();
        $this->assertFalse($model->isValid($data, false));   // isPartial = false
    }

	public function testCanGetRelationsByType()
	{

        $parents = [
           'users' => [
               'class' => 'Slender\API\Model\Users',
           ],
           'profiles' => [
               'class' => 'Slender\API\Model\Profiles',
           ],
        ];

		$model = new BaseModel;

		$model->addRelations('parents', $parents);
		$parentRelations = $model->getParentRelations();
		$this->assertArrayHasKey('users', $parentRelations);
		$this->assertArrayHasKey('profiles', $parentRelations);


		$model->addRelations('children', $parents);
		$childRelations = $model->getChildRelations();
		$this->assertArrayHasKey('users', $childRelations);
		$this->assertArrayHasKey('profiles', $childRelations);

	}

	public function testCanembeddedChildData()
	{

		$parentEntity = [
			'_id' => '123',
			'name' => 'parent',
		];

		$childEntity = [
			'_id' => '123',
			'name' => 'child',
		];

		$model = new BaseModel;
		/*
		* test case: parent has one embedded child
		*/
		$model->embeddedChildData($parentEntity, 'embedded-one', $childEntity, 'has-one');
		$this->assertArrayHasKey('embedded-one', $parentEntity);
		$this->assertArrayHasKey('name', $parentEntity['embedded-one']);
		$this->assertSame('child', $parentEntity['embedded-one']['name']);
		/*
		* test case: parent has one non-embedded child
		*/
		$model->embeddedChildData($parentEntity, 'not-embedded-one', $childEntity["_id"], 'has-one');
		$this->assertArrayHasKey('not-embedded-one', $parentEntity);
		$this->assertTrue(!is_array($parentEntity['not-embedded-one']));
		$this->assertSame('123', $parentEntity['not-embedded-one']);
		/*
		* test case: parent has many embedded children
		*/
		$model->embeddedChildData($parentEntity, 'embedded-many', $childEntity, 'has-many');
		$this->assertArrayHasKey('embedded-many', $parentEntity);
		$this->assertTrue(Utils\Arrays::isIndexed($parentEntity['embedded-many']));
		$this->assertSame('child', $parentEntity['embedded-many'][0]['name']);
		$model->embeddedChildData($parentEntity, 'embedded-many', $childEntity, 'has-many');
		$this->assertTrue(Utils\Arrays::isIndexed($parentEntity['embedded-many']));
		$this->assertSame(2, count($parentEntity['embedded-many']));

	}

	public function getEntities()
	{
		$model = new BaseModel;
		$modelSpy = $this->getMock('Slender\API\Model\BaseModel');
		$modelSpy->expects($this->any())
			->method('findById')
			->will($this->returnValue(['name'=>'photo1', 'description' => 'a pretty pic']));
		$entities = $model->getChildEntities($modelSpy, [1,2,3,4]);		
		$this->assertEquals(4, count($entities));
	}

	public function testCanCreateRelatedClass()
	{
		
		$model = new BaseModel;

		$relations = [
			'albums' => [
				'class' => 'Slender\API\Model\Albums',
				'embed' => true, // or false
				'embedKey' => 'albums',
			],
		];

		$class = $model->createRelatedClass('albums', $relations['albums']);
		$this->assertInstanceOf('Slender\API\Model\Albums',$class);

	}

}
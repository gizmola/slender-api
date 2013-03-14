<?php

namespace App\Test\Model;

use Slender\API\Model\BaseModel;
use App\Test\TestCase;
use App\Test\Mock\Model\PartialUpdateWithValidation as PartialUpdateModel;

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



	public function testEmbedChildArray()
	{

		$model = new BaseModel;

		$parentData = [
			'name' => 'album1',
			'photos' => [1],
		];

		$modelSpy = $this->getMock('Slender\API\Model\BaseModel');
		$modelSpy->expects($this->exactly(1))
			->method('findById')
			->with(1)
			->will($this->returnValue(['name'=>'photo1', 'description' => 'a pretty pic']));
		$model->embedChildData($parentData['photos'], $modelSpy);
		$this->assertInternalType('array', $parentData['photos'][0]);
		$this->assertArrayHasKey('name', $parentData['photos'][0]);
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

    public function testCanAddNewChildIds() 
    {

    	$oldData = [
    		'id' => 'id1',
    		'name' => 'name',
    		'key1' => [
	    		['item1'=>'value1'],
	    		['item2'=>'value2'],
	    		['item3'=>'value3'],
	    	],
	    	'key2' => 'some-old-data'
    	];

    	$newData = [
    		'key1' => ['id1','id2', 'id3'],
    		'key2' => 'id4',
    	];

    	$model = new BaseModel;
    	$model->addNewChildIds($oldData, $newData);
    	$this->assertEquals('id4', $oldData['key2']);
    	$this->assertEquals('id2', $oldData['key1'][4]);

    }

	public function testEmbedOneToOneChildArray()
	{

		$model = new BaseModel;

		$parentData = [
			'name' => 'album1',
			'photos' => 1,
		];

		$modelSpy = $this->getMock('Slender\API\Model\BaseModel');
		$modelSpy->expects($this->exactly(1))
			->method('findById')
			->with(1)
			->will($this->returnValue(['name'=>'photo1', 'description' => 'a pretty pic']));
		$model->embedChildData($parentData['photos'], $modelSpy);

		$this->assertInternalType('array', $parentData['photos']);
		$this->assertArrayHasKey('description', $parentData['photos']);

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




}
<?php

namespace App\Test\Model;

use Slender\API\Model\BaseModel;
use App\Test\TestCase;
use App\Test\Mock\Model\PartialUpdateWithValidation as PartialUpdateModel;
use Dws\Utils;
use Dws\Slender\Api\Cache\CacheService;

class BaseModelTest extends TestCase
{
 	
 	private function getMockConnection($data)
 	{

 		$methods = array('collection','where', 'first', 'get');
 		$mockConnection = $this->getMock('stdClass', $methods);
		
		foreach ($methods as $m) {
			if (empty($data[$m])) {
				$data[$m] = $mockConnection;
			}	
		}

		$mockConnection->expects($this->any())
			->method('collection')
			->will($this->returnValue($data['collection']));
		$mockConnection->expects($this->any())
			->method('where')
			->will($this->returnValue($data['where']));
		$mockConnection->expects($this->any())
			->method('first')
			->will($this->returnValue($data['first']));
		$mockConnection->expects($this->any())
			->method('get')
			->will($this->returnValue($data['get']));

		return $mockConnection;

 	}

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

		$model->updateParentData($childData, $parentData, 'has-many', $model::UPDATE_METHOD_DELETE);
		$this->assertEquals(1,count($parentData));
		//test that the array keys were reset
		$this->assertEquals('123456',$parentData[0]['_id']);

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

	public function testGetEntities()
	{
		$model = new BaseModel;
		$modelSpy = $this->getMock('Slender\API\Model\BaseModel');
		$modelSpy->expects($this->any())
			->method('findById')
			->will($this->returnValue(['name'=>'photo1', 'description' => 'a pretty pic']));
		$entities = $model->getEntities($modelSpy, [1,2,3,4]);		
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
	/*
	* SUT
	* BaseModel::embedChildEntities()
	* which is called when a new entity
	* is created and existing children
	* are to be embedded
	*/
	public function testEmbedChildEntities()
	{

		//build and model and set relations
		$model = new BaseModel;
		$relations = [
			'children' => [
			   'photos' => [
			       'class' => 'Slender\API\Model\Photos',
			       'embed' => true,
			       'embedKey' => 'photos',
			       'type' => 'has-many',
			   ],
			],
		];
		$model->setRelations($relations);
		/*
		* build a mock child class that will return a data
		* set representing a child entity when Model::findById($id)
		* is called
		*/
		$childModelMock = $this->getMock('Slender\API\Model\BaseModel');
		$childModelMock->expects($this->any())
			->method('findById')
			->will($this->returnValue(['_id'=>'1324', 'title' => "a child photo"]));
		/*
		* a mock resolver class allows use to 
		* alert the application that the system
		* is under test by returning an instance of
		* ChildModelMock
		*/
		$resolverMock = $this->getMock(
			'Dws\Slender\Api\Resolver\ResourceResolver',
			array('buildModelRelations'),
			array(),
			'MyResolverMock',
			false
		);
		$resolverMock->expects($this->any())
			->method('buildModelRelations')
			->will($this->returnValue($childModelMock));
		$model->setResolver($resolverMock);

		//run the actual test
		/*
		* The child list is a mock list of child 
		* ids to be embedded in the parent
		*/
		$childList = ['photos' => [1]];
		/*
		* data for a mock parent entity into which
		* the child data will be embedded
		*/
		$parentEntity = [
			'_id' => '1234',
			'photos' => [],
		];
		$model->embedChildEntities($parentEntity, $childList);
		$this->assertSame(1, count($parentEntity['photos']));

	}
	/*
	* SUT
	* BaseModel::addToParentEntities()
	* which is called when a child entity
	* is created or updated and has been 
	* assigned to existing parent(s)
	*/
	public function testAddToParentEntities()
	{

		/*
		* stub child data to be embedded in the parent entity
		*/
		$childEntity = ['_id'=>'1324', 'title' => "a child photo"];
		/*
		* stub list of parent ids
		*/
		$parentList = ['albums' => [1]];
		/*
		* build a mock parent
		*/
		$parentModelMock = $this->getMock('Slender\API\Model\BaseModel');
		/*
		* set up the child model and its relation
		* to the mock parent class
		*/
		$model = new BaseModel;
		$parentRelations = [
			'parents' => [
		    	'albums' => [
		        	'class' => get_class($parentModelMock),
		        ],
		    ],
		];
		$model->setRelations($parentRelations);
		/*
		* stub child relations
		* to be returned by 
		* parentModelMock::getChildRelations()
		*/
		$relations = [
			'children' => [
			   'photos' => [
			       'class' => get_class($model),
			       'embed' => true,
			       'embedKey' => 'photos',
			       'type' => 'has-many',
			   ],
			],
		];
		/*
		* stub data to be returned by parentModelMock
		* when findById($id) is called
		*/
		$parentEntity = [
			'_id' => '1234',
			'photos' => [],
		];
		/*
		* mock parent class will return $parentEntity
		* when Model::findById($id) is called
		* and $relations when parentModelMock::getChildRelations() is called
		*/
		$parentModelMock->expects($this->any())
			->method('getChildRelations')
			->will($this->returnValue($relations['children']));
		$parentModelMock->expects($this->any())
			->method('findById')
			->will($this->returnValue($parentEntity));
		/*
		* a mock resolver class allows us to 
		* alert the application that the system
		* is under test by returning an instance of
		* parentModelMock
		*/
		$resolverMock = $this->getMock(
			'Dws\Slender\Api\Resolver\ResourceResolver',
			array('buildModelRelations'),
			array(),
			'MyResolverMock',
			false
		);
		$resolverMock->expects($this->any())
			->method('buildModelRelations')
			->will($this->returnValue($parentModelMock));
		$model->setResolver($resolverMock);
		/*
		* Since the SUT does not return or modify and observable data, we must
		* inspect the data via an observer

		* data that is expected to be 
		* passed to update function
		*/
		$equalToParentId = '1234';
		$equalToParent = [
			'photos' => [$childEntity],
		];
		$parentModelMock->expects($this->any())
			->method('update')
			->with($this->equalTo($equalToParentId), $this->equalTo($equalToParent));
		/*
		* run the test
		*/
		$model->addToParentEntities($childEntity, $parentList);

	}

	/*
	* SUT
	* BaseModel::updateParents()
	* Which is called any time an
	* entity is updated
	*/
	public function testUpdateParents()
	{
		//echo "\n" . __FUNCTION__ . "\n";
		$model = new BaseModel;
		//$model = new \Slender\API\Model\Photos;
		/*
		* build a mock parent
		*/
		$parentModelMock = $this->getMock(
			'Slender\API\Model\BaseModel',
			[
				'getChildRelations',
				'getChildByClassName',
				'update',
				'getCollection',
				'where',
				'get'
			]
		);
		/*
		* stub child data to be updated to
		*/
		$childEntity = ['_id'=>'1324', 'title' => "a new child photo title"];
		/*
		* stub data to be returned 
		* by mockConnection
		* when get() is called
		*/
		$parentEntities = [
			[
				'_id' => '1234',
				'photos' => [
					[
						'_id'=>'1324', 
						'title' => "an old child photo title"
					],
				],
			]
		];
		/*
		* the expected data to be passed
		* to parentModel::update()
		*/
		$expectedUpdatedParentId = $parentEntities[0]['_id'];
		$expectedUpdatedParentData = [
			'photos' => [
				$childEntity
			],
		];
		/*
		* stub child relations
		* to be returned by 
		* parentModelMock::getChildRelations()
		*/
		$parentChildRelations = [
			'children' => [
			   'photos' => [
			       'class' => get_class($model),
			       'embed' => true,
			       'embedKey' => 'photos',
			       'type' => 'has-many',
			   ],
			],
		];
		/*
		* set up the child model and its relation
		* to the mock parent class
		*/
		$childParentRelations = [
			'parents' => [
		    	'albums' => [
		        	'class' => get_class($parentModelMock),
		        ],
		    ],
		];
		$model->setRelations($childParentRelations);
		/*
		* a mock resolver class allows us to 
		* alert the application that the system
		* is under test by returning an instance of
		* parentModelMock
		*/
		$resolverMock = $this->getMock(
			'Dws\Slender\Api\Resolver\ResourceResolver',
			array('buildModelRelations'),
			array(),
			'MyResolverMock',
			false
		);
		$resolverMock->expects($this->any())
			->method('buildModelRelations')
			->will($this->returnValue($parentModelMock));
		$model->setResolver($resolverMock);
		/*
		* mock parent class will return $parentEntity
		* when Model::findById($id) is called
		* and $relations when parentModelMock::getChildRelations() is called
		*/
		$parentModelMock->expects($this->any())
			->method('getChildRelations')
			->will($this->returnValue($parentChildRelations['children']));
		$parentModelMock->expects($this->any())
			->method('getChildByClassName')
			->will($this->returnValue($parentChildRelations['children']['photos']));
		/*
		* updateParents uses the Lmongo connection to find parents
		* therefore we mock the calls to it
		*/
		$parentModelMock->expects($this->any())
			->method('getCollection')
			->will($this->returnValue($parentModelMock));
		$parentModelMock->expects($this->any())
			->method('where')
			->will($this->returnValue($parentModelMock));
		$parentModelMock->expects($this->any())
			->method('get')
			->will($this->returnValue($parentEntities));
		/*
		* Since the SUT does not return or modify and observable data, we must
		* inspect the data via an observer
		*/
		$parentModelMock->expects($this->any())
			->method('update')
			->with($this->equalTo("1234"), $this->equalTo($expectedUpdatedParentData));
		/*
		* RUN THE TEST
		*/
		$model->updateParents($childEntity, false);

	}
	/*
	* SUT
	* BaseModel::findById()
	*/
	public function testFindById()
	{
		
		/*
		* data to be used in the test
		*/
		$id = 1;
		$data = [ 
			'first' => [
				'data' => 'this is not cached data',
			],
		];
		/*
		* Set up CacheService
		*/
		$requestPath = "test/url";
		$cacheConfig = ['enabled' => false, 'cache_time' => 1];
		$params = [];
		$cache = new CacheService($requestPath, $cacheConfig, $params);
		/*
		* Create a model, mock its connection
		* and set the the cache service
		*/
		$model = new BaseModel;
		$mockConnection = $this->getMockConnection($data);
		$model->setConnection($mockConnection);
		$model->setCacheService($cache);
		/*
		* forget any previous cache
		*/
		$rememberBy = $model->getCollectionName() . "_" . $id;
		$cache->forget($rememberBy);
		/*
		* This should get data from the mock connection
		*/
		$rtnData = $model->findById($id);
		$this->assertSame($data['first'],$rtnData);
		/*
		* now lets see if we can pull from cache instead
		*/
		$cachedData = [
			'data' => 'this IS cached data',
		];
		$cache->setConfig(true, 'enabled');
		$cache->putData($rememberBy, $cachedData);
		$rtnData = $model->findById($id);
		$this->assertSame($cachedData,$rtnData);

	}

}
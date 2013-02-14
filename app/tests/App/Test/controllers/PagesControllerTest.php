<?php

namespace App\Test\Controller;

use App\Test\TestCase;

class PagesControllerTest extends TestCase
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        // override site-based mongo connection for tests
		\App::singleton('MongoSiteSingleton', function(){
			return App::make('mongo')->connection('unit-tests');
		});        
    }
    
	public function testGetSingular()
	{
        $response = $this->call('GET', '/ai/pages/some-slug');
        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('pages', $response);
	}	
 
  /*
    public function testBadInsert()
    {

        $input = [
		    'id' => '123',
		    'title' => 'a title',
		    'meta' => [
				'title' => 'a title',
				'keywords' => [
					'keyword1',
					'keyword2'
				]
			],
		    'slug' => 'a-title',
		    'body' => 'some body',
		    'availability' => [
				'sunrise' => 'please fail',
				'sunset' => '2013-02-06 00:00:00'
		    ],
		    'created' => '2013-02-06 00:00:00',
		    'updated' => '2013-02-06 00:00:00'
        ];

        $response = $this->call('POST', '/pages', [], [], [], json_encode($input));
        $response = json_decode($response->getContent(), true); 
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
        $this->assertArrayHasKey('availability.sunrise', $response['messages'][0]);
    }

    public function testInsert()
    {

        $input = [
		    'id' => '123',
		    'title' => 'a title',
		    'meta' => [
				'title' => 'a title',
				'keywords' => [
					'keyword1',
					'keyword2'
				]
			],
		    'slug' => 'a-title',
		    'body' => 'some body',
		    'availability' => [
				'sunrise' => '2013-02-06 00:00:00',
				'sunset' => '2013-02-06 00:00:00'
		    ],
		    'created' => '2013-02-06 00:00:00',
		    'updated' => '2013-02-06 00:00:00'
        ];

        $response = $this->call('POST', '/pages', [], [], [], json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('pages', $response);
    }
    */


}
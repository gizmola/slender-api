<?php

class PagesControllerTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testGetSingular()
	{
        $response = $this->call('GET', '/ai/pages/some-slug');
        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('pages', $response);
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
                'sunrise' => 'please fail',
                'sunset' => '2013-02-06 00:00:00'
            ],
            'created' => '2013-02-06 00:00:00',
            'updated' => '2013-02-06 00:00:00'
        ];
 
        
 
        $response = $this->call('POST', '/pages', [], [], [], json_encode($input));
        $this->assertInternalType('array', $response);
        //$response = json_decode($response->getContent(), true);
 
 
 
        //$this->assertInternalType('array', $response);
        //$this->assertArrayHasKey('pages', $response);
 
    }
 

}
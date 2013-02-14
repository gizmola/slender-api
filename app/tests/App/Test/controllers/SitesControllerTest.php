<?php

namespace App\Test\Controller;

use App\Test\TestCase;

class SitesControllerTest extends TestCase
{

    public function testGetSingular()
    {
        $response = $this->call('GET', '/sites/slug');
        $response = json_decode($response->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('sites', $response);
    }   

	public function testBadInsert()
	{

        $input = [
            'url' => 'url',
            'title' => '',
            'slug' => 'slug'
        ];

        $response = $this->call('POST', '/sites', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
	}	

    public function testInsert()
    {

        $input = [
            'url' => 'http://google.com',
            'title' => 'Site Title',
            'slug' => ''
        ];

        $response = $this->call('POST', '/sites', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('sites', $response);
    }

}
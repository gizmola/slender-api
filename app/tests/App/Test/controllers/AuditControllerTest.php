<?php

namespace App\Test\Controller;

use \App;
use App\Test\TestCase;

class AuditControllerTest extends TestCase
{


    public function testGETSingular()
    {
        $response = $this->call('GET', '/audit/slug');
        $response = json_decode($response->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('audit', $response);
    }

	public function testPOST()
	{

        $input = [
            'uid' => 'x',
        ];

        $response = $this->call('POST', '/audit', array(), array(), array(), json_encode($input));

        $this->assertEquals(401, $response->getStatusCode());

        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
	}

    public function testPUT()
    {

        $input = [
            'uid' => 'x',
        ];

        $response = $this->call('PUT', '/audit/test', array(), array(), array(), json_encode($input));

        $this->assertEquals(401, $response->getStatusCode());

        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
    }

}

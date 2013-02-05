<?php

class UsersControllerTest extends TestCase {


    public function testGetSingular()
    {
        $response = $this->call('GET', '/users/slug');
        $response = json_decode($response->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('users', $response);
    }   

	public function testBadInsert()
	{

        $input = [
            'first_name'    => 'John',
            'last_name'     => '',
            'email'         => 'john@doe.no',
            'password'      => 'required',
        ];

        $response = $this->call('POST', '/users', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
	}	

    public function testInsert()
    {

        $input = [
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'email'         => 'john@doe.no',
            'password'      => 'required',
            'roles'         => ['492608c0-1b95-4a55-9648-928c2349fcab', '5d9deca9-6a5f-4efd-afc0-7a181262a3ab'] 
        ];

        $response = $this->call('POST', '/users', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('users', $response);

        // var_dump($response);

    }

}
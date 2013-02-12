<?php

namespace App\Test\Controller;

use App\Test\TestCase;

class UsersControllerTest extends TestCase
{

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
            'roles'         => ['492608c0-1b95-4a55-9648-928c2349fcab', '5d9deca9-6a5f-4efd-afc0-7a181262a3ab'] 
        ];

        $response = $this->call('POST', '/users', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
	}	

    public function testInsert()
    {

        $input = [
            'name' => 'Admin Role',
            'permissions' => [
                'global' => [
                    'users' => [
                        'read'      => 1,
                        'write'     => 1, 
                        'delete'    => 1,
                    ], 
                    'roles' => [
                        'read'      => 0,
                        'write'     => 0, 
                        'delete'    => 0,
                    ], 
                    'sites' => [
                        'read'      => 1,
                        'write'     => 0, 
                        'delete'    => 1,
                    ],                 
                ]
            ]
        ];

        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);

        $role1 = $response['roles'][0];
        $this->refreshApplication(); // fuck this

        $input = [
            'name' => 'Admin Role',
            'permissions' => [
                'global' => [
                    'users' => [
                        'read'      => 1,
                        'write'     => 1, 
                        'delete'    => 1,
                    ], 
                    'roles' => [
                        'read'      => 1,
                        'write'     => 0, 
                        'delete'    => 1,
                    ], 
                    'sites' => [
                        'read'      => 0,
                        'write'     => 1, 
                        'delete'    => 0,
                    ],                 
                ]
            ]
        ];

        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);

        $role2 = $response['roles'][0];
        $this->refreshApplication();   // ..and this

        $input = [
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'email'         => 'john@doe.no',
            'password'      => 'required',
            'roles'         => [(string)$role1['_id'], (string)$role2['_id']] 
        ];

        $response = $this->call('POST', '/users', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('users', $response);

        $user = $response['users'][0];
        var_dump($user);
        $this->assertContains($role1['_id'], $user['roles']);
        $this->assertContains($role2['_id'], $user['roles']);


        $this->assertEquals(1, $user['permissions']['global']['users']['read']);

        $this->assertEquals(1, $user['permissions']['global']['roles']['read']);
        $this->assertEquals(1, $user['permissions']['global']['roles']['delete']);

        $this->assertEquals(1, $user['permissions']['global']['sites']['read']);
        $this->assertEquals(1, $user['permissions']['global']['sites']['write']);
        $this->assertEquals(1, $user['permissions']['global']['sites']['delete']);
    }

}
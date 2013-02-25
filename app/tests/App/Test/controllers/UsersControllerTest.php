<?php

namespace App\Test\Controller;

use App\Test\TestCase;
use Dws\Slender\Api\Auth\Permissions;
use Slender\API\Model\Roles;

class UsersControllerTest extends TestCase
{
    /**
     * @var Roles
     */
    protected $rolesModel;

    public function setUp()
    {
        parent::setUp();
        $this->rolesModel = new Roles();
    }

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

    public function testValidInsert()
    {
        $roleIds = [];

        $role1Permissions = [
            'core' => [
                'users' => [
                    'read'      => 1,
                ],
                'roles' => [
                    'read'      => 1,
                ],
                'sites' => [
                    'read'      => 1,
                ],
            ],
        ];
        Permissions::normalize($role1Permissions);
        $role = $this->rolesModel->insert([
            'name' => 'Core Reader',
            'permissions' => $role1Permissions,
        ]);
        $roleIds[] = $role['_id'];

        $role2Permissions = [
            'core' => [
                'users' => [
                    'write'      => 1,
                ],
                'roles' => [
                    'write'      => 1,
                ],
                'sites' => [
                    'write'      => 1,
                ],
            ],
        ];
        Permissions::normalize($role2Permissions);
        $role = $this->rolesModel->insert([
            'name' => 'Core Write',
            'permissions' => $role2Permissions,
        ]);
        $roleIds[] = $role['_id'];

        $input = [
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'email'         => 'john@doe.no',
            'password'      => 'required',
            'roles'         => $roleIds,
        ];

        $response = $this->call('POST', '/users', array(), array(), array(), json_encode($input));
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('users', $response);
        $records = $response['users'];
        $this->assertInternalType('array', $records);
        $this->assertCount(1, $records);
        $user = $records[0];
        $this->assertInternalType('array', $user);

        $this->assertArrayHasKey('roles', $user);
        $this->assertCount(2, $user['roles']);

        $this->assertArrayHasKey('permissions', $user);
        $permissions = $user['permissions'];

        // no global privileges
        $this->assertEquals(0, $permissions['_global']['read']);
        $this->assertEquals(0, $permissions['_global']['write']);
        $this->assertEquals(0, $permissions['_global']['delete']);

        // some core privileges
        $this->assertEquals(1, $permissions['core']['users']['read']);
        $this->assertEquals(1, $permissions['core']['users']['write']);
        $this->assertEquals(0, $permissions['core']['users']['delete']);

        $this->assertEquals(1, $permissions['core']['roles']['read']);
        $this->assertEquals(1, $permissions['core']['roles']['write']);
        $this->assertEquals(0, $permissions['core']['roles']['delete']);

        $this->assertEquals(1, $permissions['core']['sites']['read']);
        $this->assertEquals(1, $permissions['core']['sites']['write']);
        $this->assertEquals(0, $permissions['core']['sites']['delete']);
    }
}
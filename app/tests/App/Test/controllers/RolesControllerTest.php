<?php

namespace App\Test\Controller;

use \App;
use App\Test\TestCase;

class RolesControllerTest extends TestCase
{


    public function testGETSingular()
    {
        $response = $this->call('GET', '/roles/slug');
        $response = json_decode($response->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('roles', $response);
    }

	public function testPOSTValidateName()
	{

        $input = [
            'name' => 'x',
            'permissions' => [
                'global' => [
                    'users' => [
                        'read'      => 1,
                        'write'     => 1,
                        'delete'    => 1,
                    ],
                    'roles' => [
                        'read'      => 1,
                        'write'     => 1,
                        'delete'    => 1,
                    ],
                    'sites' => [
                        'read'      => 1,
                        'write'     => 1,
                        'delete'    => 1,
                    ],
                ]
            ]
        ];

        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($input));

        $response = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('messages', $response);
	}

//	public function testPOSTValidatePermissionBoolean()
//	{
//
//        $input = [
//            'name' => 'A long enough name',
//            'permissions' => [
//                'global' => [
//                    'users' => [
//                        'read'      => 1,
//                        'write'     => 'some crazy invalid thinggggs',
//                        'delete'    => 1,
//                    ],
//                    'roles' => [
//                        'read'      => 1,
//                        'write'     => 1,
//                        'delete'    => 1,
//                    ],
//                    'sites' => [
//                        'read'      => 1,
//                        'write'     => 1,
//                        'delete'    => 1,
//                    ],
//                ]
//            ]
//        ];
//
//        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($input));
//
//        $response = json_decode($response->getContent(), true);
//
//        $this->assertInternalType('array', $response);
//        $this->assertArrayHasKey('messages', $response);
//	}
//
    public function testPOSTWithValidData()
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
                        'read'      => 1,
                        'write'     => 1,
                        'delete'    => 1,
                    ],
                    'sites' => [
                        'read'      => 1,
                        'write'     => 1,
                        'delete'    => 1,
                    ],
                ]
            ]
        ];

        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($input));
        $response = json_decode($response->getContent(), true);
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('roles', $response);
    }

    /**
     * @group auth
     */
    public function testThatPOSTRoleInExcessOfClientPermissionsFails()
    {
        // create a client
        $clientData = [
            'first_name' => 'John',
            'last_name' => 'Client',
            'email' => 'johnclient@exmaple.com',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'read' => 1,
                            'write' => 0,
                            'delete' => 0,
                    ],
                ],
            ],
                ],
        ];

        // set client into the IoC container
        App::singleton('client-user', function() use ($clientData) {
                    return $clientData;
                });

        // create a role with permissions exceeding the client
        $input = [
            'name' => 'Excessive Role',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'read' => 1,
                            'write' => 1,
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
        ];

        // assert 401 reject
        $response = $this->call('POST', '/roles', array(), array(), array(), json_encode($input));
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @group auth
     */
    public function testThatPUTRoleInExcessOfClientPermissionsFails()
    {
        // create a client
        $clientData = [
            'first_name' => 'John',
            'last_name' => 'Client',
            'email' => 'johnclient@exmaple.com',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'read' => 1,
                            'write' => 0,
                            'delete' => 0,
                    ],
                ],
            ],
                ],
        ];

        // set client into the IoC container
        App::singleton('client-user', function() use ($clientData) {
                    return $clientData;
                });

        // create a role with permissions exceeding the client
        $input = [
            'name' => 'Excessive Role',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'read' => 1,
                            'write' => 1,
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
        ];

        // assert 401 reject
        $response = $this->call('PUT', '/roles/123', array(), array(), array(), json_encode($input));
        $this->assertEquals(401, $response->getStatusCode());
    }

}
<?php

namespace App\Test\Model;

use App\Test\TestCase;
use Dws\Slender\Api\Auth\Permissions;
use Slender\API\Model\Roles;
use Slender\API\Model\Users;

/**
 * Test the Roles model
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class RolesTest extends TestCase
{
    /**
     * @var Roles
     */
    protected $rolesModel;

    /**
     * @var Users
     */
    protected $usersModel;

    public function setUp()
    {
        parent::setUp();
        $this->rolesModel = new Roles();
        $this->usersModel = new Users();
    }

    /**
     * This is so bad. We use the SUT to perform the initial inserts that
     * should be relegated to fixtures.
     * @todo
     */
    public function testThatUpdateRoleTriggersModificationOfAffectedUsers()
    {
        // create some roles
        $roleIds = [];
        $role = $this->rolesModel->insert([
            'name' => 'AI News reader',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'news' => [
                            'read' => 1,
                        ],
                    ]
                ],
            ],
        ]);
        $roleIds[] = $role['_id'];

        $role = $this->rolesModel->insert([
            'name' => 'AI News Writer',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'news' => [
                            'write' => 1,
                        ],
                    ]
                ],
            ],
        ]);
        $roleIds[] = $role['_id'];
        $roleIdToUpdate = $role['_id'];

        // create user with those roles
        $user = $this->usersModel->insert([
            'first_name' => 'John',
            'last_name' => 'Userton',
            'email' => 'john@exmaple.com',
            'password' => 'asdf',
            'roles' => $roleIds,
        ]);
        $userId = $user['_id'];

        // update one of the roles
        $this->rolesModel->update($roleIdToUpdate, [
            'name' => 'AI News Editor',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'news' => [
                            'write' => 1,
                            'delete' => 1,
                        ],
                    ]
                ],
            ],
        ]);

        // confirm that user roles and permissions reflect role modification
        $user = $this->usersModel->findById($userId, true);
        $permissions = new Permissions($user['permissions']);
        $this->assertTrue($permissions->isSameAs([
            'per-site' => [
                'ai' => [
                    'news' => [
                        'read' => 1,
                        'write' => 1,
                        'delete' => 1,
                    ],
                ]
            ],
        ]));
    }

    /**
     * This is so bad. We use the SUT to perform the initial inserts that
     * should be relegated to fixtures.
     * @todo
     */
    public function testThatDeleteRoleTriggersModificationOfAffectedUsers()
    {
        // create some roles
        $roleIds = [];
        $role = $this->rolesModel->insert([
            'name' => 'AI News reader',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'news' => [
                            'read' => 1,
                        ],
                    ]
                ],
            ],
        ]);
        $roleIds[] = $role['_id'];

        $role = $this->rolesModel->insert([
            'name' => 'AI News Writer',
            'permissions' => [
                'per-site' => [
                    'ai' => [
                        'news' => [
                            'write' => 1,
                        ],
                    ]
                ],
            ],
        ]);
        $roleIds[] = $role['_id'];
        $roleIdToDelete = $role['_id'];

        // create user with those roles
        $user = $this->usersModel->insert([
            'first_name' => 'John',
            'last_name' => 'Userton',
            'email' => 'john@exmaple.com',
            'password' => 'asdf',
            'roles' => $roleIds,
        ]);
        $userId = $user['_id'];

        // delete one of the roles
        $this->rolesModel->delete($roleIdToDelete);

        // confirm that user roles and permissions reflect role modification
        $user = $this->usersModel->findById($userId, true);
        $permissions = new Permissions($user['permissions']);
        $this->assertTrue($permissions->isSameAs([
            'per-site' => [
                'ai' => [
                    'news' => [
                        'read' => 1,
                    ],
                ]
            ],
        ]));
    }
}

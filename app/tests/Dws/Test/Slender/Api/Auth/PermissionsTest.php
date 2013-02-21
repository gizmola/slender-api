<?php

namespace Dws\Test\Slender\Api\Auth;

use Dws\Slender\Api\Auth\Permissions;

/**
 * Tests for the permissions class
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class PermissionsTest extends \PHPUnit_Framework_TestCase
{

    public function dataProviderTestCanWriteUsertoSite()
    {
        $data = [];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ], 'ai', true
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 0,
                        ],
                    ],
                ],
            ], 'ai', false
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ], 'txf', false
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ], 'txf', false
        ];

        $data[] = [
            [
                'per-site' => [
                    'txf' => [
                        'users' => [
                            'write' => 0,
                        ],
                    ],
                ],
            ], 'ai', false,
        ];

        $data[] = [
            [
                'core' => [
                    'users' => [
                        'write' => 1,
                    ],
                ],
            ], null, true,
        ];

        $data[] = [
            [
                'core' => [
                    'users' => [
                        'write' => 0
                    ],
                ],
            ], null, false,
        ];

        $data[] = [
            [
                'core' => [
                    'users' => [
                        'write' => 0,
                    ],
                ],
            ], 'ai', false,
        ];

        return $data;
    }

    /**
     * @covers canWriteUerToSite
     * @dataProvider dataProviderTestCanWriteUsertoSite
     * @param array $permissionsData
     * @param string|null $site
     * @param boolean $result
     */
    public function testCanWriteUserToSite($permissionsData, $site, $result)
    {
        $permissions = new Permissions($permissionsData);
        $assertMethod = 'assert' . ($result ? 'True' : 'False');
        $this->$assertMethod($permissions->canWriteUserToSite($site));
    }

    public function dataProviderTestCanWriteRoletoSite()
    {
        $data = [];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'roles' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ], 'ai', true
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'roles' => [
                            'write' => 0,
                        ],
                    ],
                ],
            ], 'ai', false
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'roles' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ], 'txf', false
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'roles' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ], 'txf', false
        ];

        $data[] = [
            [
                'per-site' => [
                    'txf' => [
                        'roles' => [
                            'write' => 0,
                        ],
                    ],
                ],
            ], 'ai', false,
        ];

        $data[] = [
            [
                'core' => [
                    'roles' => [
                        'write' => 1,
                    ],
                ],
            ], null, true,
        ];

        $data[] = [
            [
                'core' => [
                    'roles' => [
                        'write' => 0
                    ],
                ],
            ], null, false,
        ];

        $data[] = [
            [
                'core' => [
                    'roles' => [
                        'write' => 0,
                    ],
                ],
            ], 'ai', false,
        ];

        return $data;
    }

    /**
     * @covers canWriteRoleToSite
     * @dataProvider dataProviderTestCanWriteRoletoSite
     * @param array $permissionsData
     * @param string|null $site
     * @param boolean $result
     */
    public function testCanWriteRoleToSite($permissionsData, $site, $result)
    {
        $permissions = new Permissions($permissionsData);
        $assertMethod = 'assert' . ($result ? 'True' : 'False');
        $this->$assertMethod($permissions->canWriteRoleToSite($site));
    }

    public function dataProviderTestIsAtLeast()
    {
        $data = [];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            true,
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 0,
                        ],
                    ],
                ],
            ],
            true,
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            true,
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            false,
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            false,
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            false,
        ];

        $data[] = [
            [
                'core' => [
                    'users' => [
                        'write' => 1,
                    ],
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'core' => [
                    'users' => [
                        'write' => 1,
                    ],
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            false,
        ];

        // exceed "or equal" is ok, too
        $data[] = [
            [
                'core' => [
                    'users' => 1,
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'core' => [
                    'users' => 1,
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            true,
        ];

        return $data;
    }

    /**
     * @covers isAtLeast
     * @dataProvider dataProviderTestIsAtLeast
     * @param array $permissionsData
     * @param string|null $site
     * @param boolean $result
     */
    public function testIsAtLeast($permissionsData, $otherPermissionsData, $result)
    {
        $permissions = new Permissions($permissionsData);
        $otherPermissions = new Permissions($otherPermissionsData);
        $assertMethod = 'assert' . ($result ? 'True' : 'False');
        $this->$assertMethod($permissions->isAtLeast($otherPermissions));
    }

    public function dataProviderTestCreatePermissionList()
    {
        $data = [];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site.ai.users.write',
            ],
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site.ai.users.write',
            ],
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site.ai.users.read',
                'per-site.ai.users.write',
            ],
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 0,
                        ],
                    ],
                ],
            ],
            [
                'per-site.ai.users.read',
            ],
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site.ai.users.write',
                'per-site.txf.users.write',
            ],
        ];

        $data[] = [
            [
                'core' => [
                    'users' => [
                        'write' => 1,
                    ],
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'core.users.write',
                'per-site.ai.users.write',
                'per-site.txf.users.write',
            ],
            false,
        ];

        return $data;
    }

    /**
     * @covers createPermissionList
     * @dataProvider dataProviderTestCreatePermissionList
     * @param array $permissionsData
     * @param array $list
     */
    public function testCreatePermissionList($permissionsData, $list)
    {
        $permissions = new Permissions($permissionsData);
        $this->assertSame($permissions->createPermissionList(), $list);
    }

    public function dataProviderTestAddPermissions()
    {
        $data = [];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $data[] = [
            [
                'core' => [
                    'roles' => [
                        'write' => 1,
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
            [
                'core' => [
                    'roles' => [
                        'write' => 1,
                    ],
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * @covers addPermissions
     * @dataProvider dataProviderTestAddPermissions
     * @param array $permissionsData
     * @param array $otherPermissionsData
     * @param boolean $result
     */
    public function testAddPermissions($permissionsData, $otherPermissionsData, $result)
    {
        $permissions = new Permissions($permissionsData);
        $otherPermissions = new Permissions($otherPermissionsData);
        $expectedPermissions = new Permissions($result);

        // Shouldn't actually be using another method of the SUT
        // (hasSamePermissions()) - since that method might be faulty.
        // But it's so useful here. And that method has its own tests.
        // #rationalization #weak
        $this->assertTrue(
            $permissions->addPermissions($otherPermissions)
                ->hasSamePermissions($expectedPermissions));
    }

    public function dataProviderTestHasSamePermissions()
    {
        $data = [];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $data[] = [
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $data[] = [
            [
                'core' => [
                    'roles' => [
                        'write' => 1,
                    ],
                ],
            ],
            [
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
            [
                'core' => [
                    'roles' => [
                        'write' => 1,
                    ],
                ],
                'per-site' => [
                    'ai' => [
                        'users' => [
                            'read' => 1,
                        ],
                    ],
                    'txf' => [
                        'videos' => [
                            'delete' => 1,
                        ],
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * @covers hasSamePermissions
     * @dataProvider dataProviderTestHasSamePermissions
     * @param array $permissionsData
     * @param array $otherPermissionsData
     * @param boolean $result
     */
    public function testHasSamePermissions($permissionsData, $otherPermissionsData, $result)
    {
        $permissions = new Permissions($permissionsData);
        $otherPermissions = new Permissions($otherPermissionsData);
        $expectedPermissions = new Permissions($result);
        $this->assertTrue(
            $permissions->addPermissions($otherPermissions)
                ->hasSamePermissions($expectedPermissions));
    }

}

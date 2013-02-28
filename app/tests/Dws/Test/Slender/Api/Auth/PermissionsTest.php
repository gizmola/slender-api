<?php

namespace Dws\Test\Slender\Api\Auth;

use Dws\Slender\Api\Auth\Permissions;
use Dws\Slender\Api\Support\Util\Arrays as ArrayUtil;

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
     * @covers canWriteUserToSite
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

//        $data[] = [
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            true,
//        ];
//
//        $data[] = [
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 0,
//                        ],
//                    ],
//                ],
//            ],
//            true,
//        ];
//
//        $data[] = [
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'read' => 1,
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            true,
//        ];
//
//        $data[] = [
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'read' => 1,
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            false,
//        ];
//
//        $data[] = [
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                    'txf' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'read' => 1,
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            false,
//        ];
//
//        $data[] = [
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                    'txf' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'read' => 1,
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            false,
//        ];
//
//        $data[] = [
//            [
//                'core' => [
//                    'users' => [
//                        'write' => 1,
//                    ],
//                ],
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                    'txf' => [
//                        'users' => [
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'core' => [
//                    'users' => [
//                        'write' => 1,
//                    ],
//                ],
//                'per-site' => [
//                    'ai' => [
//                        'users' => [
//                            'read' => 1,
//                            'write' => 1,
//                        ],
//                    ],
//                ],
//            ],
//            false,
//        ];

        // exceed "or equal" is ok, too
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
                '_global' => [
                    'read' => 0,
                    'write' => 0,
                    'delete' => 0,
                ],
                'core' => [
                    'users' => [
                        'read' => 0,
                        'write' => 0,
                        'delete' => 0,
                    ],
                    'roles' => [
                        'read' => 0,
                        'write' => 0,
                        'delete' => 0,
                    ],
                    'sites' => [
                        'read' => 0,
                        'write' => 0,
                        'delete' => 0,
                    ],
                ],
                'per-site' => [
                    'ai' => [
                        '_global' => [
                            'read' => 0,
                            'write' => 0,
                            'delete' => 0,
                        ],
                        'users' => [
                            'read' => 1,
                            'write' => 1,
                            'delete' => 0,
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
     * @param boolean $$expectedData
     */
    public function testAddPermissions($permissionsData, $otherPermissionsData, $expectedData)
    {
        $permissions = new Permissions($permissionsData);

        $modifiedPermissions = $permissions->addPermissions($otherPermissionsData);
        $modifiedPermissions = $modifiedPermissions->toArray();

        // Now need to compare the structures. But I can't find a good away to
        // compare two multidimensional arrays. So, we'll just cheat and generate
        // sorted, one-dimensional arrays that we can compare easily.

        $expectedPermissionsList = (new Permissions($expectedData))->createPermissionList();
        $modifiedPermissionsList = (new Permissions($modifiedPermissions))->createPermissionList();

        sort($expectedPermissionsList);
        sort($modifiedPermissionsList);

        $this->assertSame($expectedPermissionsList, $modifiedPermissionsList);
    }


    public function dataProviderTestNormalize()
    {
        return [

            // set 0, read-only global
            [
                // initial
                [
                    '_global' => [
                        'read' => 1,
                    ]
                ],

                // expected
                [
                    '_global' => [
                        'read' => 1,
                        'write' => 0,
                        'delete' => 0,
                    ],
                    'core' => [
                        'users' => [
                            'read' => 0,
                            'write' => 0,
                            'delete' => 0,
                        ],
                        'roles' => [
                            'read' => 0,
                            'write' => 0,
                            'delete' => 0,
                        ],
                        'sites' => [
                            'read' => 0,
                            'write' => 0,
                            'delete' => 0,
                        ],
                    ],
                    'per-site' => [

                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderTestNormalize
     * @param array $initialPermissions
     * @param array $expectedPermissions
     */
    public function testNormalize($permissions, $expectedPermissions)
    {
        Permissions::normalize($permissions);
        $this->assertSame($expectedPermissions, $permissions);
    }

    public function testGetSupercedingGlobals()
    {
        // No superceding global for a global permissions
        $this->assertSame([], Permissions::getSupercedingGlobals('_global.read'));

        // Only globals permissions can supercede core permissions
        $this->assertSame(['_global.read'], Permissions::getSupercedingGlobals('core.users.read'));

        // Both global and per-site global permissions supercede per-site resource permissions
        $this->assertSame([
            '_global.read',
            'per-site.ai._global.read',
        ], Permissions::getSupercedingGlobals('per-site.ai.users.read'));
    }

}

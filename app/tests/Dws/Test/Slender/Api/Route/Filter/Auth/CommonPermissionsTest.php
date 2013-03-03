<?php

namespace Dws\Test\Slender\Api\Route\Filter\Auth;

use Dws\Slender\Api\Auth\Permissions;
use Dws\Slender\Api\Resolver\ResourceResolver;
use Dws\Slender\Api\Route\Filter\Auth\CommonPermissions as AuthFilter;
use Illuminate\Http\Request;

/**
 * Test for the common-permissions auth filter
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class CommonPermissionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Build a mock request
     *
     * @param string $method
     * @param string $url
     * @return Request
     */
    protected function buildMockRequest($method, $url)
    {
        $request = $this->getMock('Illuminate\Http\Request', ['getMethod', 'segments'], [], '', false);

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue($method));

        $request->expects($this->any())
                ->method('segments')
                ->will($this->returnValue(explode('/', trim($url, '/'))));
        return $request;
    }

    /**
     * Build a user from perms
     *
     * @param array $perms
     * @return array
     */
    protected function buildUserFromPermissions($perms)
    {
        Permissions::normalize($perms);
        return [
            'first_name' => 'Tom',
            'last_name' => 'Userton',
            'email' => 'aaa@xxx.com',
            'permissions' => $perms,
        ];
    }

    /**
     * Build a mock resource resolver
     *
     * @param string $requestType
     * @return ResourceResolver
     */
    protected function buildMockResourceResolver($requestType)
    {
        $resolver = $this->getMock('Dws\Slender\Api\Resolver\ResourceResolver', ['getRequestType'], [], '', false);

        $resolver->expects($this->once())
                ->method('getRequestType')
                ->will($this->returnValue($requestType));

        return $resolver;
    }

    /**
     * Date provider for auth tests
     *
     * @return array
     */
    public function dataProviderTestAuthenticate()
    {
        $data = [];

        // test core resources
        foreach (['sites', 'users', 'roles'] as $resource) {

            foreach (['singular', 'plural'] as $sp) {

                // (read, no perms) => false
                $data[] = [
                    'GET',
                    $resource . ('singular' == $sp ? '/123' : ''),
                    'core',
                    [],
                    false,
                    $sp . ' read request on ' . $resource . ' with empty perms should fail auth',
                ];

                // (read, global read perms) => true
                $data[] = [
                    'GET',
                    $resource . ('singular' == $sp ? '/123' : ''),
                    'core',
                    [
                        '_global' => [
                            'read' => 1.
                        ],
                    ],
                    true,
                    $sp . ' read request on ' . $resource . ' with global read perms should pass auth',
                ];

                // (read, global write perms) => false
                $data[] = [
                    'GET',
                    $resource . ('singular' == $sp ? '/123' : ''),
                    'core',
                    [
                        '_global' => [
                            'write' => 1.
                        ],
                    ],
                    false,
                    $sp . ' read request on ' . $resource . ' with global write perms should fail auth',
                ];

                foreach (['POST', 'PUT'] as $method) {

                    // (write, no perms) => false
                    $data[] = [
                        $method,
                        $resource . ('singular' == $sp ? '/123' : ''),
                        'core',
                        [],
                        false,
                        $sp . ' write request on ' . $resource . ' with empty perms should fail auth',
                    ];

                    // (write, global write perms) => true
                    $data[] = [
                        $method,
                        $resource . ('singular' == $sp ? '/123' : ''),
                        'core',
                        [
                            '_global' => [
                                'write' => 1,
                            ],
                        ],
                        true,
                        $sp . ' write request on ' . $resource . ' with global write perms should pass auth',
                    ];

                    // (write, global read perms) => false
                    $data[] = [
                        $method,
                        $resource . ('singular' == $sp ? '/123' : ''),
                        'core',
                        [
                            '_global' => [
                                'read' => 1,
                            ],
                        ],
                        false,
                        $sp . ' write request on ' . $resource . ' with global read perms should fail auth',
                    ];
                }

                // DELETE
                // (write, global delete perms) => true
                $data[] = [
                    'DELETE',
                    $resource . ('singular' == $sp ? '/123' : ''),
                    'core',
                    [
                        '_global' => [
                            'delete' => 1,
                        ],
                    ],
                    true,
                    'Singular delete request on ' . $resource . ' with global delete perms should pass auth',
                ];
            }

            // Spot-check some core-resource privilege combos
            // Too many to do them all. Well, actually, we could paramaterize better,
            // but this is probably adequate. For now. <cue ominous music>

            // read, per-resource read => true
            $data[] = [
                'GET',
                $resource,
                'core',
                [
                    'core' => [
                        $resource => [
                            'read' => 1,
                        ],
                    ],
                ],
                true,
                'Plural read request on ' . $resource . ' with ' . $resource . ' read perms should pass auth',
            ];

            // read, per-resource write => false
            $data[] = [
                'GET',
                $resource,
                'core',
                [
                    'core' => [
                        $resource => [
                            'write' => 1,
                        ],
                    ],
                ],
                false,
                'Plural read request on ' . $resource . ' with ' . $resource . ' write perms should fail auth',
            ];

            // write, per-resource write => true
            $data[] = [
                'POST',
                $resource,
                'core',
                [
                    'core' => [
                        $resource => [
                            'write' => 1,
                        ],
                    ],
                ],
                true,
                'Plural write request on ' . $resource . ' with ' . $resource . ' write perms should pass auth',
            ];

            // write, per-resource read => false
            $data[] = [
                'POST',
                $resource,
                'core',
                [
                    $resource => [
                        'read' => 1,
                    ],
                ],
                false,
                'Plural write request on ' . $resource . ' with ' . $resource . ' read perms should fail auth',
            ];

        }

        // now some per-site tests with empty perms
        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [],
            false,
            'Plural per-site read request empty perms should fail auth',
        ];
        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [],
            false,
            'Plural per-site write request with empty perms should fail auth',
        ];
        $data[] = [
            'DELETE',
            'ai/videos',
            'per-site',
            [],
            false,
            'Plural per-site delete request with empty perms should fail auth',
        ];
        $data[] = [
            'PUT',
            'ai/videos/123',
            'per-site',
            [],
            false,
            'Singular per-site write request with empty perms should fail auth',
        ];
        $data[] = [
            'DELETE',
            'ai/videos',
            'per-site',
            [],
            false,
            'Singular per-site delete request with empty perms should fail auth',
        ];

        // add some global perms
        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [
                '_global' => [
                    'read' => 1,
                ],
            ],
            true,
            'Plural per-site read request with global read perms should pass auth',
        ];

        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [
                '_global' => [
                    'write' => 1,
                ],
            ],
            false,
            'Plural per-site read request with global write perms should fail auth',
        ];

        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [
                '_global' => [
                    'write' => 1,
                ],
            ],
            true,
            'Plural per-site write request with global write perms should pass auth',
        ];

        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [
                '_global' => [
                    'read' => 1,
                ],
            ],
            false,
            'Plural per-site write request with global read perms should fail auth',
        ];

        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        '_global' => [
                            'read' => 1,
                        ],
                    ],
                ],
            ],
            true,
            'Plural per-site read request with relevant per-site global read perms should pass auth',
        ];

        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        '_global' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            false,
            'Plural per-site read request with relevant per-site global write perms should fail auth',
        ];

        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        '_global' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            true,
            'Plural per-site write request with relevant per-site global write perms should pass auth',
        ];

        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        '_global' => [
                            'read' => 1,
                        ],
                    ],
                ],
            ],
            false,
            'Plural per-site write request with relevant per-site global read perms should fail auth',
        ];

        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'read' => 1,
                        ],
                    ],
                ],
            ],
            true,
            'Plural per-site read request with relevant per-site resource read perms should pass auth',
        ];

        $data[] = [
            'GET',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            false,
            'Plural per-site read  request with relevant per-site resource write perms should fail auth',
        ];

        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'write' => 1,
                        ],
                    ],
                ],
            ],
            true,
            'Plural per-site write request with relevant per-site resource write perms should pass auth',
        ];

        $data[] = [
            'POST',
            'ai/videos',
            'per-site',
            [
                'per-site' => [
                    'ai' => [
                        'videos' => [
                            'read' => 1,
                        ],
                    ],
                ],
            ],
            false,
            'Plural per-site write request with relevant per-site resource read perms should fail auth',
        ];

        return $data;
    }

    /**
     * @dataProvider dataProviderTestAuthenticate
     * @param string $url
     * @param string $method
     * @param array $clientPerms
     * @param boolean $expected
     */
    public function testAuthenticate($method, $url, $requestType, $clientPerms, $expected, $message)
    {
        $request = $this->buildMockRequest($method, $url);
        $clientUser = $this->buildUserFromPermissions($clientPerms);
        $resolver = $this->buildMockResourceResolver($requestType);
        $auth = new AuthFilter($request, $clientUser, $resolver);
        $assertMethod = $expected ? "assertTrue" : "assertFalse";
        $this->$assertMethod($auth->authenticate(), $message);
    }
}

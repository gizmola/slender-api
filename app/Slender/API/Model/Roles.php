<?php

namespace Slender\API\Model;

class Roles extends BaseModel
{

    protected $collectionName = 'roles';

    /**
    * To test validation call: curl -X POST -d '{"name": "Admin Role", "permissions": {"global": {"roles": {"delete": 1, "read": 1, "write": 0}, "users": {"delete": 1, "read": 1, "write": 0}, "sites": {"delete": 1, "read": 1, "write": 0}}}}'  http://localhost:4003/roles
    */
    protected $schema = [
        'name' => ['required', 'min:5'],
        'permissions' => [
            'global' => [
                'users' => [
                    'read'      => ['required', 'boolean'],
                    'write'     => ['required', 'boolean'],
                    'delete'    => ['required', 'boolean'],
                ],
                'roles' => [
                    'read'      => ['required', 'boolean'],
                    'write'     => ['required', 'boolean'],
                    'delete'    => ['required', 'boolean'],
                ],
                'sites' => [
                    'read'      => ['required', 'boolean'],
                    'write'     => ['required', 'boolean'],
                    'delete'    => ['required', 'boolean'],
                ],
            ],
            'per-site' => [],
        ]
    ];
}

<?php

namespace Slender\API\Model;

use Dws\Slender\Api\Auth\Permissions;

class Roles extends BaseModel
{

    protected $collectionName = 'roles';

    /**
    * To test validation call: curl -X POST -d '{"name": "Admin Role", "permissions": {"global": {"roles": {"delete": 1, "read": 1, "write": 0}, "users": {"delete": 1, "read": 1, "write": 0}, "sites": {"delete": 1, "read": 1, "write": 0}}}}'  http://localhost:4003/roles
    */
    protected $schema = [
        'name' => ['required', 'min:5'],
        'permissions' => [
            '_global' => [
                'read'      => ['boolean'],
                'write'     => ['boolean'],
                'delete'    => ['boolean'],
            ],
            'core' => [
                'users' => [
                    'read'      => ['boolean'],
                    'write'     => ['boolean'],
                    'delete'    => ['boolean'],
                ],
                'roles' => [
                    'read'      => ['boolean'],
                    'write'     => ['boolean'],
                    'delete'    => ['boolean'],
                ],
                'sites' => [
                    'read'      => ['boolean'],
                    'write'     => ['boolean'],
                    'delete'    => ['boolean'],
                ],
            ],
            'per-site' => [],
        ]
    ];

    public function insert(array $data)
    {
        Permissions::normalize($data['permissions']);
        unset($data['password_confirmation']);
        return parent::insert($data);
    }

    public function update($id, array $data)
    {
        Permissions::normalize($data['permissions']);
        unset($data['password_confirmation']);
        $entity = parent::update($id, $data);
        if (!$entity) {
            return false;
        }
        return $this->updatePermissionsForUsersWithRoleId($id, false) ? $entity : false;
    }

    public function delete($id)
    {
        // We should be able to do this using standard updateParents().
        // But I'm not sure it really works. As of this writing, the signature for
        // BaseModel::updateParents() is:
        //
        // <code>public function updateParents($entity)</code>
        //
        // but the usage in BaseModel::delete($id) is:
        //
        // $this->updateParents($id);
        //
        // The usage accepts an $id but the signature accepts an $entity.
        //
        // So, I'm writing this for roles as a custom thing for now.

        if (!parent::delete($id)) {
            return false;
        }
        return $this->updatePermissionsForUsersWithRoleId($id, true);
    }

    protected function updatePermissionsForUsersWithRoleId($roleId, $isDeleted = false)
    {
        $usersModel = new Users();
        $users = $usersModel->findManyByRoleId($roleId);
        $userData = [];
        foreach ($users as $user) {
            if (isset($user['roles']) && is_array($user['roles'])) {
                if ($isDeleted) {
                    $user['roles'] = array_diff($user['roles'], [$roleId]);
                }
            }

            // mongo cursor won't allow me to update here, so gonna aggregate
            // all the users and loop again. Lame.
            $userData[] = $user;
        }
        unset($users); // clear the mongo cursor?


        // now loop through the users and update.
        // need to reset the mongo cursor?
        foreach ($userData as $user) {
            $userId = $user['_id'];
            unset($user['_id']);
            unset($user['password']);
            if (!$usersModel->update($userId, $user)) {
                return false;
            }
        }
        return true;
    }
}

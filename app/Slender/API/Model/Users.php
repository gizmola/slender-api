<?php

namespace Slender\API\Model;

use Dws\Slender\Api\Auth\Permissions;
use Dws\Slender\Api\Support\Util\Arrays as ArrayUtil;

class Users extends BaseModel
{
    protected $collectionName = 'users';

    protected $timestamp = true;

    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        'roles'         => ['required', 'array'],
        'permissions'   => [],
    ];


    public function insert(array $data, $updatePermissions = true)
    {
        if ($updatePermissions) {
            $data = self::updateRolesAndPermissions($data);
        }
        $data['password'] = \Hash::make($data['password']);
        $data['key'] = sha1(time() . str_shuffle($data['email']));

        return parent::insert($data);
    }

    public function update($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = \Hash::make($data['password']);
        }
        $data = self::updateRolesAndPermissions($data);
        return parent::update($id, $data);

    }

    public static function updateRolesAndPermissions(array $userData)
    {
        if (isset($userData['roles'])) {

            if (!is_array($userData['roles'])) {
                $userData['roles'] = (array) $userData['roles'];
            }
            $rolesModel = new Roles();

            $user_roles = $rolesModel->whereIn('_id', $userData['roles'])->get();

            $userData['roles'] = [];
            $userData['permissions'] = [];

            $permissions = new Permissions($userData['permissions']);

            foreach ($user_roles as $key => $value) {
                $userData['roles'][] = $value['_id'];
                $permissions->addPermissions($value['permissions']);
                // ArrayUtil::array_unset_recursive($value['permissions'], 0);
                // $data['permissions'] = array_replace_recursive($data['permissions'], $value['permissions']);
            }
            $userData['permissions'] = $permissions->toArray();
        }
        // Permissions::normalize($data['permissions']);  // necessary?
        return $userData;
    }

    /**
     * Find user by key
     *
     * @param string $key
     * @return array
     */
    public function findByKey($key)
    {
        return $this->getCollection()->where('key', $key)->first();
    }

    public function findManyByRoleId($roleId)
    {
        return $this->getCollection()->where('roles', $roleId)->get();
    }
}

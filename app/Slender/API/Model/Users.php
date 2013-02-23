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


    public function insert(array $data)
    {
        $data = $this->updateRolesAndPermissions($data);
        $data['password'] = \Hash::make($data['password']);
        $data['key'] = sha1(time() . str_shuffle($data['email']));

        return parent::insert($data);
    }

    public function update($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = \Hash::make($data['password']);
        }
        $data = $this->updateRolesAndPermissions($data);
        return parent::update($id, $data);

    }

    private function updateRolesAndPermissions(array $data)
    {
        if (isset($data['roles'])) {

            if (!is_array($data['roles'])) {
                $data['roles'] = (array) $data['roles'];
            }
            $roles = new Roles();

            $user_roles = $roles->whereIn('_id', $data['roles'])->get();

            $data['roles'] = array();
            $data['permissions'] = array();

            foreach ($user_roles as $key => $value) {
                $data['roles'][] = $value['_id'];

                ArrayUtil::array_unset_recursive($value['permissions'], 0);
                $data['permissions'] = array_replace_recursive($data['permissions'], $value['permissions']);
            }
        }
        Permissions::normalize($data['permissions']);
        return $data;
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
}

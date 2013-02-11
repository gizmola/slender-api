<?php

namespace App\Model;

class Users extends BaseModel
{

    protected $collectionName = 'users';

    protected $timestamp = true;

    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        // 'key'           => [],
        'roles'         => ['required:array'],
        'permissions'   => [],
    ];


    public function insert(array $data)
    {
        if(isset($data['roles'])){
            if(!is_array($data['roles']))
            {
                $data['roles'] = (array) $data['roles'];
            }
            $roles = new Roles();
            $user_roles = $roles->whereIn('_id', $data['roles'])->get();
            $data['roles'] = array();
            $data['permissions'] = array();

            foreach ($user_roles as $key => $value)
            {
                $data['roles'][] = $value['_id'];

                $this->array_unset_recursive($value['permissions'], 0);
                $data['permissions'] = array_replace_recursive($data['permissions'], $value['permissions']);
            }
        }


        $data['key'] = sha1(time() . str_shuffle($data['email']));

        return parent::insert($data);
    }

    private function array_unset_recursive(&$array, $remove) 
    {
        if (!is_array($remove)) $remove = array($remove);
        foreach ($array as $key => &$value) {
            if (in_array($value, $remove)) unset($array[$key]);
            else if (is_array($value)) {
                $this->array_unset_recursive($value, $remove);
            }
        }
    }

}
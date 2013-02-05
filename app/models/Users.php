<?php

class Users extends BaseModel{

    protected $collectionName = 'users';

    // Add
    // - created
    // - updated

    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        'key'           => [],
        'roles'         => [],
        'permissions'   => [],
    ];
}
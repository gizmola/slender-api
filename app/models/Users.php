<?php

class Users extends BaseModel{

    protected $collectionName = 'users';

    protected $timestamp = true;

    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        'key'           => ['required'],
        'roles'         => ['required'],
        'permissions'   => [],
    ];
}
<?php

class Roles extends BaseModel{

    protected $collectionName = 'roles';

    protected $schema = [
        'name' => [
            //'type:string', 
            'required', 'min:50'],
        'permissions' => [
            'global' => [
                'users' => [
                    'read'      => ['required', 'boolean'],
                    'write'     => ['required', 'boolean'], 
                    'delete'    => ['required', 'boolean'],
                ], 
                'roles' => [
                    'read'      => ['required'],
                    'write'     => ['required'], 
                    'delete'    => ['required'],
                ], 
                'sites' => [
                    'read'      => ['required'],
                    'write'     => ['required'], 
                    'delete'    => ['required'],
                ],                 
            ]
        ]
    ];


}
<?php

namespace Slender\API\Model;


class Members extends BaseModel
{

    protected $collectionName = 'members';
        
    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        'active'      => ['in:0,1'],
    ];

}
<?php

namespace Slender\API\Model\Site\Eb;

use \Slender\API\Model\BaseModel as BaseModel;

class Users extends BaseModel
{
    
    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        'active'      => ['in:0,1'],
        'vendor_profiles' => [],
        'customer_profiles' => [],
    ];

}

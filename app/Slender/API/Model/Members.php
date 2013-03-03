<?php

namespace Slender\API\Model;

use \Slender\API\Model\BaseModel as BaseModel;

class Members extends BaseModel
{
    
    protected $schema = [
        'first_name'    => ['required'],
        'last_name'     => ['required'],
        'email'         => ['required', 'email'],
        'password'      => ['required'],
        'active'      => ['in:0,1'],
        'facebook_id' => ['alpha_dash'],
        'twitter_id' => ['alpha_dash'],
        'activation_key' => [],
    ];

}
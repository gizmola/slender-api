<?php

namespace Slender\API\Model;


class Profiles extends BaseModel
{

    protected $collectionName = 'profiles';
        
    protected $schema = [
        'phone'    => ['alpha_num'],
        'address'     => ['alpha_num'],
        'zip'         => ['alpha_num'],
        'city'      => ['alpha_num'],
        'state/province' => ['alpha_num'],
        'country' => ['alpha_num'],
        'birthday' => ['date'],
    ];
    
}
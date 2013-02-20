<?php

namespace Slender\API\Model;


class Profiles extends BaseModel
{

    protected $collectionName = 'profiles';
        
    protected $schema = [
        'member_id' => ['alpha_dash'], 
        'phone'    => ['alpha_dash'],
        'address'     => ['regex:/^[0-9A-Za-z _-]+$/'],
        'zip'         => ['alpha_dash'],
        'city'      => ['regex:/^[0-9A-Za-z _-]+$/'],
        'state/province' => ['regex:/^[0-9A-Za-z _-]+$/'],
        'country' => ['regex:/^[0-9A-Za-z _-]+$/'],
        'birthday' => ['date'],
    ];
    
}
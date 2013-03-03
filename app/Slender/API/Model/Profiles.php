<?php

namespace Slender\API\Model;


class Profiles extends BaseModel
{

    protected $collectionName = 'profiles';
        
    protected $schema = [
        'member_id' => ['alpha_dash'],
        'location' => [
            'phone'    => ['alpha_dash'],
            'address1'     => ['regex:/^[0-9A-Za-z _-]+$/'],
            'address2'     => ['regex:/^[0-9A-Za-z _-]+$/'],
            'zip'         => ['alpha_dash'],
            'city'      => ['regex:/^[0-9A-Za-z _-]+$/'],
            'state/province' => ['regex:/^[0-9A-Za-z _-]+$/'],
        ],
    ];
    
}
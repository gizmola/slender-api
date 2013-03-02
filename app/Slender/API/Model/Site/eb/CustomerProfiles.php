<?php

namespace Slender\API\Model\Site\Eb;

use \Slender\API\Model\Profiles as BaseModel;


class CustomerProfiles extends BaseModel
{

    protected $collectionName = 'customer_profiles';
        
    protected $extendedSchema = [
        'birthday' => ['date'],
    ];
    
}
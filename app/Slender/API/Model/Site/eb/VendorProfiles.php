<?php

namespace Slender\API\Model\Site\Eb;

use \Slender\API\Model\Profiles as BaseModel;


class VendorProfiles extends BaseModel
{

    protected $collectionName = 'vendor_profiles';
        
    protected $extendedSchema = [
        'business_name'     => ['regex:/^[0-9A-Za-z _-]+$/'],
        'service_categories' => [],
        'keywords' => [],
        'website_link',
        'youtube_link'
    ];
    
}
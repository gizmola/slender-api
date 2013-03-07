<?php

namespace Slender\API\Model\Site\Eb;

use \Slender\API\Model\BaseModel as BaseModel;

class ServiceCategories extends BaseModel
{

    protected $collectionName = 'service_categories';

    protected $schema = [
        'name'    => ['required'],
    ];

}
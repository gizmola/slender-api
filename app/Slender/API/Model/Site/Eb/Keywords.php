<?php

namespace Slender\API\Model\Site\Eb;

use \Slender\API\Model\BaseModel as BaseModel;

class Keywords extends BaseModel
{

    protected $collectionName = 'keywords';
    
    protected $schema = [
        'name'    => ['required'],
    ];

}
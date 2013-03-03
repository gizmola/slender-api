<?php

namespace Slender\API\Model\Site\Eb;

use \Slender\API\Model\Members as BaseModel;

class Users extends BaseModel
{
    protected $extendedSchema = [
        'customer-profiles' => [],
        'vendor-profiles' => [],
    ];
}
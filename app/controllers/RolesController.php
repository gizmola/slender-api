<?php

use Dws\Slender\Api\Validation\ValidationException;


class RolesController extends BaseController
{
	protected $returnKey = 'roles';

    public function __construct(Roles $model)
    {
        parent::__construct($model);
    }


   
}
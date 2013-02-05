<?php

class RolesController extends BaseController
{
	protected $returnKey = 'roles';

    public function __construct(Roles $model)
    {
        parent::__construct($model);
    }


}
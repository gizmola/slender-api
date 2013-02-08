<?php

class UsersController extends BaseController
{
	protected $returnKey = 'users';

    public function __construct(Users $model)
    {
        parent::__construct($model);
    }
}
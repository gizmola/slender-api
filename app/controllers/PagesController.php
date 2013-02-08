<?php

class PagesController extends BaseController
{
	protected $returnKey = 'pages';

    public function __construct(Pages $model)
    {
        parent::__construct($model);
    }
}
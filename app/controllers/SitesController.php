<?php

class SitesController extends BaseController
{
	protected $returnKey = 'sites';

    public function __construct(Sites $model)
    {
        parent::__construct($model);
    }
}
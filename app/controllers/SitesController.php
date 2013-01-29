<?php

class SitesController extends BaseController
{
	protected $returnKey = 'sites';

	protected $container = 'sites';


	public function httpGetSingular($id=null)
	{
		// access to the $container collection 
		// var_dump($this->sites->findOne());

		// $site = array('name' => 'ai', 'data' => 1);

		// Sites::insert($site);

		$sites = $this->sites->find();
		
		$res = array();
		foreach ($sites as $key => $value) {
			$res[] = $value;
		}
		return Response::json(array(
			$this->getReturnKey() => array(
				$res
			),
		));
	}


	public function httpOptionsSingular($id)
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}


}
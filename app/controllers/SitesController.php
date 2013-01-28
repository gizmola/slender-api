<?php

class SitesController extends BaseController
{
	protected $returnKey = 'sites';

	public function httpGetSingular($id=null)
	{

		// $site = array('name' => 'ai', 'data' => 1);

		// Sites::insert($site);

		$sites = Sites::find();
		
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
<?php


class SitesController extends BaseController
{
	protected $returnKey = 'sites';

	protected $container = 'sites';

	protected $model = 'App\Models\Sites';


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
			$this->getReturnKey() => $res
		));
	}

	// public function httpOptionsSingular()
	// {
	// 	// access to the $container collection 
	// 	// var_dump($this->sites->findOne());

	// 	// $site = array('name' => 'ai', 'data' => 1);

	// 	// Sites::insert($site);

		// die('111');
	// 	$sites = $this->sites->find();
	// 	$res = array();
	// 	foreach ($sites as $key => $value) {
	// 		$res[] = $value;
	// 	}
	// 	return Response::json(array(
	// 		$this->getReturnKey() => $res
	// 	));
	// }


	public function httpOptionsSingular($id=null)
	{
		$model = $this->getModel();

		return Response::json(array(
			'POST' => array(
				'description' => '',
				'parameters' => array(
					$model::$schema
				),
			),
		));

	}


}
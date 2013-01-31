<?php

namespace App\Controller\Site\Ai;

use App\Model\Site\Ai\Albums,
	AlbumsController as BaseAlbumsController;


/**
 * AlbumsController for the AI site
 *
 * @author Juni Samos <juni.samos@diamondwebservices.com>
 */
class AlbumsController extends BaseAlbumsController
{
	
	/**
	 * Constructor
	 * 
	 * Needed simply for type-hinting so that Laravel can auto-instantiate and
	 * inject the model
	 * 
	 * @param \App\Model\Site\Ai\Albums $model
	 */
	public function __construct(Albums $model)
	{
		parent::__construct($model);
	}
	
	/**
	 * For demonstration purposes. In principle, this would be missing, handled by
	 * the general implementation in BaseController. Included here just to demonstrate
	 * that the connection points to the `slender_ai` db and the `pages` collection.
	 */
	// public function index()
	// {
	// 	echo "<pre>" . var_dump($this->model) . "</pre>";
	// 	die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	// }

}

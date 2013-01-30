<?php

namespace App\Controller\Site\Ai;

use App\Model\Site\Ai\Pages;


/**
 * PagesController for the AI site
 *
 * @author Juni Samos <juni.samos@diamondwebservices.com>
 */
class PagesController extends \PagesController
{

	protected $returnKey = 'pages';

	public function __construct(Pages $model)
	{
		$this->model = $model;
	}
}

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
	protected $site = 'ai';

	public function view($id)
	{
		return '{"pages":[{ "_id" : "id", "title" : "page title"}]}';
	}

}

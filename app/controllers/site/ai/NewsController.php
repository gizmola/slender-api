<?php

namespace App\Controller\Site\Ai;

use \NewsController as BaseNewsController;
use \News;
use \Response;
use App\Model\Site\Ai;

/**
 * NewsController for the AI site
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class NewsController extends BaseNewsController
{
	protected $site = 'ai';	
	
	protected $model = '\App\Model\Site\Ai\News';
	
	protected $returnKey = 'news';
	
	public function view($id)
	{
		

		$news = new $this->model;

		// insert
		// $news->insert( array('email' => 'john@example.com', 'votes' => 0));
		
		// get first
		// $one = $news->where('email', 'john@example.com')->first();
		// var_dump($one);

		// get all 
		$result = $news->get();

		// @TODO: move result processing (including _id) to the BaseModel
		$return = array();
		foreach ($result as $value)
		{
		    $return[] =$value;
		}
		// var_dump(\App::make('mongo')->connection('ai'));
		
		// sample
		return Response::json(array(
			$this->getReturnKey() => $return
		));
	}

	public function index()
	{
		// sample override just
		return Response::json(array(
			$this->getReturnKey() => array(
				array(
					'id' => 111,
					'headline' => 'My Headline 1',
					'slug' => 'my-slug-1',
				),
				array(
					'id' => 222,
					'headline' => 'My Headline 1',
					'slug' => 'my-slug-1',
				),
				array(
					'id' => 333,
					'headline' => 'My Headline 1',
					'slug' => 'my-slug-1',
				),
			),
		));
	}

}

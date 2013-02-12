<?php

namespace Slender\API\Model\Site\Demo;

use \Slender\API\Model\News as BaseNews;

class News extends BaseNews
{
	public function findById($id)
	{
		return [
            'id' => $id,
            'title' => 'My title via overriden model',
        ];
	}
}
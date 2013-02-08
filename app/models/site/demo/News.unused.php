<?php

namespace App\Model\Site\Demo;

use \News as BaseNews;

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
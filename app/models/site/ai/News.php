<?php

namespace App\Model\Site\Ai;

use \News as BaseNews;

/**
 * A News model for the AI site
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class News extends BaseNews
{
	public static $schema = array(

        'name' => array('type' => 'string'),


	);
}

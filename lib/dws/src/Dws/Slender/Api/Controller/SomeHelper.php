<?php

namespace Dws\Slender\Api\Controller;

/**
 * A helper
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class SomeHelper
{
	public static function help()
	{
		die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
	}
}

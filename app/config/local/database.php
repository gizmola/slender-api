<?php

$config = array(		
	'default' => array(
	    'driver'   => 'mongodb',
		'host' => '127.0.0.1',
	    'port'     => 27017,
	    //'username' => 'username',
	    //'password' => 'password',
		'database' => 'slender',
	),
	'mysite' => array(
	    'driver'   => 'mongodb',
		'host' => '127.0.0.1',
	    'port'     => 27017,
	    //'username' => 'username',
	    //'password' => 'password',
		'database' => 'mysite',
	),
);

$argv = Request::instance()->server->get('argv');

if ($argv) {

	foreach ($argv as $key => $value) {
		// For the console environment, we'll just look for an argument that starts
		// with "--env" then assume that it is setting the environment for every
		// operation being performed, and we'll use that environment's config.
		if (starts_with($value, '--site='))
		{
			
			$segments = array_slice(explode('=', $value), 1);
			$site = head($segments);

		}

	}

} else {

	$segments = Request::segments();

	if (count($segments) > 1) {

		$site = Request::segment(1);

	}

}

if (!isset($site)) 
	$site = 'default';


$config = array('connections' => array('mongodb' => $config[$site]));


return $config;


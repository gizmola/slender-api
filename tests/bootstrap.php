<?php

require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function($class){
	$file = __DIR__ . '/mocks/my/' . str_replace('\\', '/', $class) . '.php';
	if (file_exists($file)){
		include $file;
		return $class;
	}
	return false;
});

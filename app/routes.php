<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{

	// some example
	$role = array(
	 'role' => 'Test role',
	 'site1' => array(
	 		'read'		=> 1,
	 		'write'		=> 1,
	 		'delete'	=> 0
	 	)
	);

	// Role::insert($role);

	var_dump(Role::all());


	// return Response::json(array('status' => 'ok'), 200);
	// return View::make('hello');
});
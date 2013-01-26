<?php

use Dws\Slender\Api\Controller\SomeHelper;

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

Route::get('/', function(){
	die('OK');
});

Route::get('sample-home', 'SampleHomeController@showWelcome');

Route::get('help', function(){
	SomeHelper::help();
});

App::missing(function($exception)
{
    return View::make('errors.missing');
});
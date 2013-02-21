<?php

// System-check route
Route::get('/', function(){
    return "OK";
});

Route::post('auth', 'Slender\API\Controller\AuthController@post');
Route::post('eb/auth', 'Slender\API\Controller\Site\Eb\AuthController@post');

// Add all routes
App::make('route-creator')->addRoutes();
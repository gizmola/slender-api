<?php

// System-check route
Route::get('/', function(){
    return "OK";
});

Route::post('auth', 'Slender\API\Controller\AuthController@post');

// Add all routes
App::make('route-creator')->addRoutes();

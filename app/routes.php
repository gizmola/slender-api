<?php

// System-check route
Route::get('/mysite/test', function(){

    $env = App::environment();
    print_r($env);

    $db = DB::connection('mongodb');

    return "OK";
});

Route::post('auth', 'Slender\API\Controller\AuthController@post');

// Add all routes
//App::make('route-creator')->addRoutes();

<?php

// System-check route
Route::get('/mysite/test', function(){

    $env = App::environment();
    print_r($env);

    $db = DB::connection('mysite')->collection('users')->insert(array('name'=>'name'));

    print_r($db);

    return "OK";
});

Route::post('auth', 'Slender\API\Controller\AuthController@post');

// Add all routes
//App::make('route-creator')->addRoutes();

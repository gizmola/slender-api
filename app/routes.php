<?php

// System-check route
Route::get('/', function(){
    return "OK";
});

Route::post('auth', 'Slender\API\Controller\AuthController@post');
Route::any('eb/auth', 'Slender\API\Controller\Site\Eb\AuthController@post');

// Get our generic route-creator from the IoC container
$creator = App::make('route-creator');

// Add core routes
$creator->addCoreRoutes();

// Add site-based routes
$creator->addSiteRoutes([
    
    // ai has a lot of resources, for example
    'ai' => [
        'news',
        'photos',
        'albums',
        'videos',
        'pages',
    ],
    
    // A site for experiment/demo stuff
    'demo' => [
        'news',
    ],


    'eb' => [
        'members'
    ],

]);

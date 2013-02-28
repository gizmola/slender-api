<?php

// System-check route
Route::get('/', function(){
    return "OK";
});


// Add all routes
App::make('route-creator')->addRoutes();
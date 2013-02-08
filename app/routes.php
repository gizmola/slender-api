<?php

// System-check route
Route::get('/', function(){
    return "OK";
});

/**
 * Add non-site dependent resources
 */
Route::addRestResource('roles');
Route::addRestResource('users');
Route::addRestResource('pages');

// Is this really non-site dependent?
Route::addRestResource('pages');

/**
 * Config for the site-based-resources.
 * 
 * In principle, I expect that this will be stored in the cross-site db and then 
 * accessed via a model like SiteBasedResources. Then we can manage them via 
 * a bunch of site-independent routes, like we do with users and roles.
 * 
 * The upshot is that for each site, we simply list the resources we want to be 
 * available. We then construct routes - really only two routes, 
 * a singular and a plural one for each HTTP method we support - that, when matched 
 * instantiate the right models, and controllers and call the correct controller
 * method.
 * 
 * For example, a request for /ai/news would invoke the following pseudo-code:
 * 
 * if (news is enabled for ai){
 * 
 *    if (class_exists(App/Model/Site/Ai/News){
 *       use this model
 *    } else if (class_exists(News){
 *       use that model
 *    } else {
 *       404
 *    }
 * 
 *    if (class_exists(App/Controller/Site/Ai/News){
 *       use this controller
 *    } else if (class_exists(News){
 *       use that controller
 *    } else {
 *       404
 *    }
 * 
 *    // Inject connection into model
 *    // Inject model into controller
 *    // Call relevant method on controller
 *
 * } else {
 *    return 404
 * }
 * 
 * There is a lot of default handling implicit in here. But I expect that it 
 * will allow us to:
 * 
 * 1. Define base resource controllers and models
 * 2. Allow us to override on a per-site, per-resource basis
 * 3. Does not *require* that we create per-site class/model skeletons
 * 4. 
 */
$siteBasedResourceConfig = array(
    
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
);
App::make('site-based-resource-route-creator')->addRoutes($siteBasedResourceConfig);
unset($siteBasedResourceConfig);  // It's next to godliness, you know.

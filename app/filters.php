<?php

use \Auth;
use \App;
use Dws\Slender\Api\Resolver\ResourceResolver;
use Dws\Slender\Api\Route\Filter\Auth\CommonPermissions as CommonPermissionsAuth;
use Dws\Slender\Api\Support\Util\String as StringUtil;
use Illuminate\Session\TokenMismatchException;
use \Input;
use \Route;
use \Redirect;
use \Request;
use \Session;
use Slender\API\Model\Users as UserModel;

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/


// Initialize the user-model and client-user in the container

App::singleton('user-model', function(){
    return new UserModel();
});
App::singleton('client-user', function(){
    return null;
});

//App::before(function($request)
//{
//	//
//});
//
//
//App::after(function($request, $response)
//{
//	//
//});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. Also, a "guest" filter is
| responsible for performing the opposite. Both provide redirects.
|
*/

Route::filter('auth-common-permissions', function($route, $request)
{

    /**
     *  @todo: figure out how to plug our needs into the MongoAuthManager so
     * that we can just do something like
     *
     * <code>
     *  if (!Auth::stateless($credentials)){
     *      // Return 401
     *  }
     *  </code>
     *
     * For now, we'll just our own custom object. After all, since we will
     * authenticate on every request and never have to manage a session, there's
     * really no benefit to using Laravel's Auth class
     */

    // $request = Request::instance();
    $key = $request->header('Authentication');

    $user = App::make('client-user');
    if (!$user) {
        $user = App::make('user-model')->findByKey($key);
        App::singleton('client-user', function() use ($user){
            return $user;
        });
    }
    $resourceResolver = App::make('resource-resolver');

    $auth = new CommonPermissionsAuth($request, $user, $resourceResolver);

    if (!$auth->authenticate()) {
        return Response::json(array(
            'messages' => array(
                'Unauthorized',
            ),
        ), 401);
    }
});

//Route::filter('auth-core-modify', function($route, $request) {
//
//    // Expect the user to be stored by auth-common-permissions already
//    $user = App::make('client-user');
//    if (!$user) {
//        return Response::json(array(
//            'messages' => array(
//                'Unauthorized',
//            ),
//        ), 401);
//    }
//
//    $segments = $request->segments();
//    if (ResourceResolver::RESOURCE_TYPE_CORE != App::make('resource-resolver')->getRequestType($segments)) {
//        return;
//    }
//    $resource = $segments[0];
//    $method = strtoupper($request->getMethod());
//    if (!in_array($method, array('POST', 'PUT'))) {
//        return;
//    }
//    $authClass = 'Dws\Slender\Api\Route\Filter\Auth\Core\Modify\\' . StringUtil::camelize($resource);
//    $auth = new $authClass($request, $user);
//    if (!$auth->authenticate()) {
//        return Response::json(array(
//            'messages' => array(
//                'Unauthorized',
//            ),
//        ), 401);
//    }
//});
//

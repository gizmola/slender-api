<?php

use \Auth;
use \App;
use Dws\Slender\Api\Auth\AuthHandler;
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

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	//
});

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

Route::filter('auth', function()
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

    $request = Request::instance();
    $userModel = new UserModel();
    $resourceResolver = App::make('resource-resolver');
    $handler = new AuthHandler($request, $userModel, $resourceResolver);
    if (!$handler->authenticate()) {
        return Response::json(array(
            'messages' => array(
                'Unauthorized',
            ),
        ), 401);
    }

    /*
    $segments = $request->segments();


    $key = Request::header('AUTHENTICATION');
    $permissionPaths  = App::make('permissions-resolver')->getPermissionsPaths('.');
    $users = new Users();
    $user = $users->getCollection()
        ->where('key', $key)
//        ->where(function($query) use ($permissionPaths) {
//                $query->where('permissions._global', 1);
//                foreach ($permissionPaths as $path) {
//                    $query->where("permissions.{$path}", 1);
//                }
//            }, '$or')
        ->first();

    if (!$user) {
        return Response::json(array(
            'messages' => array(
                'Unauthorized',
            ),
        ), 401);
    }
     */
});

Route::filter('guest', function()
{
	if (Auth::check()) {
        return Redirect::to('/');
    }
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::getToken() != Input::get('csrf_token'))
	{
		throw new TokenMismatchException;
	}
});
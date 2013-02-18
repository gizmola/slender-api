<?php

use \Auth;
use \App;
use Illuminate\Session\TokenMismatchException;
use \Input;
use \Route;
use \Redirect;
use \Session;
use Slender\API\Model\Users;

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
    $key = Request::header('AUTHENTICATION');
    $requestPaths  = App::make('permissions-resolver')->getPermissionsPaths('.');
    $users = new Users();
    $user = $users->getCollection()
        ->where('key', $key)
//        ->where(function($query) use ($requestPaths) {
//                $query->where('permissions.global', 1);
//                if (isset($requestPaths['site'])){
//                    $query->where("permissions.{$requestPaths['site']}", 1);
//                }
//                if (isset($requestPaths['resource'])){
//                    $query->where("permissions.{$requestPaths['resource']}", 1);
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
});

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
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
<?php

namespace Slender\API\Controller;

use \Slender\API\Model\Users;

class AuthController extends \Slender\API\Controller\BaseController
{
	protected $returnKey = 'users';

    public function __construct() //Users
    {       
        $this->model = new Users;
    }

    /**
     * Handles HTTP POST method on a plual endpoint
     * 
     * @return mixed
     */
    public function post()
    {
        $input = \Input::json(true);
        
        $email = isset($input['email']) ? $input['email'] : '';
        $password = isset($input['password']) ? $input['password'] : '';
  
        $entity = $this->model
                    ->getCollection()
                    ->where('email', $email)
                    // ->where('password', \Hash::make($password))
                    ->first();

        $response = [];
        if(isset($entity['password']) && \Hash::check($password, $entity['password'])){
            unset($entity['password']);
            $response = $entity;
        }
        return \Response::json(array(
            $this->getReturnKey() => 
                $response
            ,
        ), self::HTTP_POST_OK);
    }
}

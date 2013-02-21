<?php

namespace Slender\API\Controller\Site\Eb;

use \Slender\API\Model\Members;

class AuthController extends \Slender\API\Controller\BaseController
{
    protected $returnKey = 'members';

    public function __construct() //Users
    {       
        $this->model = new Members;
    }

    /**
     * Handles HTTP POST method on a plual endpoint
     * 
     * @return mixed
     */
    public function post()
    {
        $email = \Input::all();
        $where = [['email',$email]];
        $meta = [];
        
        //$results = $this->model->getCollection()->where('email',$email)->get();

        $results = $this->model->findMany($where,[],[],$meta);

        $data = [];

        //foreach ($results as $doc) {

        //}

        print_r($email);
    }
}

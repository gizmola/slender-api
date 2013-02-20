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
        die("authme");
    }
}

<?php

namespace Slender\API\Controller;

use \App;
use \Input;
use \Response;

class VideodistributionsController extends \Slender\API\Controller\BaseController
{
    protected $returnKey = 'videodistributions';

    public function view($id)
    {
        $meta = [];
        $records = $this->model->findMany(array(array('video_id', $id)),array(),array(),$meta);

        return Response::json(array(
            $this->getReturnKey() => ($records ? $records : array()),
        ));
    }
}
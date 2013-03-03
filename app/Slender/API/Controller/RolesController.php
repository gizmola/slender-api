<?php

namespace Slender\API\Controller;

use \Response;
use \Validator;

class RolesController extends \Slender\API\Controller\BaseController
{
	protected $returnKey = 'roles';

    public function update($id)
    {
        $input = $this->getJsonBodyData();

//        $schema = $this->model->getSchemaValidation();
//
//        $valid = [];
//
//        // Why selectively? PUT must contain a full representation of the object,
//        // just like in POST.
//        foreach ($schema as $k => $v) {
//            if (in_array($k, array_keys($input))) {
//                $valid[$k] = $v;
//            }
//        }
//
//        if (!$valid) {
//            throw new \Exception("No valid parameters sent");
//        }

        $validator = Validator::make(
            $input,
            $this->model->getSchemaValidation()
        );

        if ($validator->fails()) {
            return $this->badRequest($validator->messages());
        }

        if (!$this->validatePayloadAgainstClient($input)) {
            return $this->unauthorizedRequest([
                'Unauthorized: proposed role permissions in excess of client permissions',
            ]);
        }

		$entity = $this->model->update($id, $input);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_PUT_OK);
    }

    public function insert()
    {
        $input = $this->getJsonBodyData();

        $validator = Validator::make(
            $input,
            $this->model->getSchemaValidation()
        );

        if ($validator->fails()) {
            return $this->badRequest($validator->messages());
        }

        if (!$this->validatePayloadAgainstClient($input)) {
            return $this->unauthorizedRequest([
                'Unauthorized: proposed role permissions in excess of client permissions',
            ]);
        }

		$entity = $this->model->insert($input);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_POST_OK);

    }
}
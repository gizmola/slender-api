<?php

namespace Slender\API\Controller;

use \Response;
use \Validator;
use Slender\API\Model\Users;

class UsersController extends BaseController
{
	protected $returnKey = 'users';

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
                'Unauthorized: proposed user permissions in excess of client permissions',
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

        // add in all the roles and permissions
        $input = Users::updateRolesAndPermissions($input);

        if (!$this->validatePayloadAgainstClient($input)) {
            return $this->unauthorizedRequest([
                'Unauthorized: proposed user permissions in excess of client permissions',
            ]);
        }

        $entity = $this->model->insert($input, false);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_POST_OK);
    }

}
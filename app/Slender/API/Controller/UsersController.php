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

        $validator = $this->makeCustomValidator($input);

        if ($validator->fails()) {
            return $this->badRequest($validator->messages());
        }

        $input = $this->model->updateRolesAndPermissions($input);

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

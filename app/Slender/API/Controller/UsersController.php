<?php

namespace Slender\API\Controller;

use \Response;
use \Validator;
use Dws\Slender\Api\Auth\Permissions;
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
            return Response::json([
                'messages' => [
                    'Unauthorized: proposed permissions in excess of client permissions',
                ],
            ], 401);
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
            return Response::json([
                'messages' => [
                    'Unauthorized: proposed permissions in excess of client permissions',
                ],
            ], 401);
        }

        $entity = $this->model->insert($input, false);
		return Response::json(array(
			$this->getReturnKey() => array(
				$entity,
			),
		), self::HTTP_POST_OK);
    }

    protected function validatePayloadAgainstClient($input)
    {
        // get client user permissions
        $clientUser = $this->getClientUser();

        // The client-user is populated by the common-permission filter, which doesn't
        // run during unit-tests. So, just skip this if he hasn't been populated.
        if (!$clientUser) {
            return true;
        }
        $clientPermissions = new Permissions($clientUser['permissions']);

        $proposedPermissions = new Permissions($input['permissions']);

        return $clientPermissions->isAtLeast($proposedPermissions);
    }
}
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

        if (!$this->model->isValid($input, true)) {
            return $this->badRequest($this->model->getValidationMessages());
        }

        $input = $this->model->updateRolesAndPermissions($input);

        if(isset($input['permissions'])){
            if (!$this->validatePayloadAgainstClient($input)) {
                return $this->unauthorizedRequest([
                    'Unauthorized: proposed user permissions in excess of client permissions',
                ]);
            }
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

        if (!$this->model->isValid($input, false)) {
            return $this->badRequest($this->model->getValidationMessages());
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

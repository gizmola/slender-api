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

        if (!$this->model->isValid($input, true)) {
            return $this->badRequest($this->model->getValidationMessages());
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

    public function insert($input = null)
    {
        $input = $this->getJsonBodyData();

        if (!$this->model->isValid($input, false)) {
            return $this->badRequest($this->model->getValidationMessages());
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

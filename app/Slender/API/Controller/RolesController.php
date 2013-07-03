<?php

namespace Slender\API\Controller;

use \Response;
use \Validator;
use \Config;

class RolesController extends \Slender\API\Controller\BaseController
{
	protected $returnKey = 'roles';

    public function update($id, $input = null)
    {
        $input = $input ?: $this->getJsonBodyData();

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
        $input = $input ?: $this->getJsonBodyData();

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

    /**
     * Handles HTTP OPTIONS method on role endpoint
     *
     * @return mixed
     */
    public function options()
    {
        $resources = [];
        $per_site = Config::get('resources.per-site');
        foreach ($per_site as $site => $value) {
            $resources[$site] = array_keys($value);
        }
        $options = $this->model->options();
        return Response::json(array(
            'PUT' => $options,
            'endpoints' => $resources
        ), self::HTTP_OPTIONS_OK);
    }
}
